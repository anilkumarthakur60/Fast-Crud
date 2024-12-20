<?php

use Anil\FastApiCrud\Tests\TestSetup\Models\PostModel;
use Anil\FastApiCrud\Tests\TestSetup\Models\UserModel;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

beforeAll(function () {

    UserModel::factory()->count(30)->create()->each(function (UserModel $user): void {
        PostModel::factory()->count(5)->create(['user_id' => $user->id]);
    });
});

it('adds the likeWhere macro to Builder', function () {
    $query = PostModel::query()->likeWhere(['title', 'desc'], 'Test');

    // Assert that the where clauses are applied
    // Since we don't have actual data matching 'Test', we can check the SQL
    $sql = $query->toSql();
    expect($sql)->toContain('where (');

    // Check that 'LIKE' is used
    expect($sql)->toContain('LIKE');
});

it('adds the paginates macro to Builder', function () {
    // Simulate a request with 'rowsPerPage'
    $this->withSession(['rowsPerPage' => 10]);

    $perPage = 10;
    $query = PostModel::query()->paginates();

    expect($query)->toBeInstanceOf(LengthAwarePaginator::class);
    expect($query->perPage())->toBe($perPage);
});

it('adds the simplePaginates macro to Builder', function () {
    // Simulate a request with 'rowsPerPage'
    $this->withSession(['rowsPerPage' => 5]);

    $perPage = 5;
    $query = PostModel::query()->simplePaginates();

    expect($query)->toBeInstanceOf(Paginator::class);
    expect($query->perPage())->toBe($perPage);
});

it('adds the initializer macro to Builder with filters and sorting', function () {
    // Simulate a request with filters and sorting
    request()->merge([
        'filters' => json_encode(['title' => 'Sample']),
        'sortBy' => 'title',
        'descending' => false,
    ]);

    $query = PostModel::query()->initializer();

    // Assert that the filter is applied
    expect($query->getQuery()->wheres)->toHaveCount(1);
    expect($query->getQuery()->wheres[0]['type'])->toBe('Basic');
    expect($query->getQuery()->wheres[0]['column'])->toBe('title');
    expect($query->getQuery()->wheres[0]['operator'])->toBe('LIKE');

    // Assert that the sorting is applied
    $orders = $query->getQuery()->orders;
    expect($orders)->toHaveCount(1);
    expect($orders[0]['column'])->toBe('title');
    expect($orders[0]['direction'])->toBe('asc');
});

it('adds the withAggregates macro to Builder', function () {
    $query = PostModel::query()->withAggregates(['comments' => 'count']);

    // Assert that the aggregate is applied
    $with = $query->getQuery()->getEagerLoads();
    expect($with)->toHaveKey('comments_count');
});

it('adds the withCountWhereHas macro to Builder', function () {
    $query = PostModel::query()->withCountWhereHas('comments', function ($q) {
        $q->where('comment', 'like', '%test%');
    }, '>=', 2);

    // Assert that the whereHas is applied
    $sql = $query->toSql();
    expect($sql)->toContain('exists');

    // Assert that withCount is applied
    $with = $query->getQuery()->getEagerLoads();
    expect($with)->toHaveKey('comments');
});

it('adds the orWithCountWhereHas macro to Builder', function () {
    $query = PostModel::query()
        ->withCountWhereHas('comments', function ($q) {
            $q->where('comment', 'like', '%test%');
        }, '>=', 2)
        ->orWithCountWhereHas('comments', function ($q) {
            $q->where('comment', 'like', '%example%');
        }, '>=', 3);

    // Assert that the orWhereHas is applied
    $sql = $query->toSql();
    expect($sql)->toContain('or exists');

    // Assert that withCount is applied for both relations
    $with = $query->getQuery()->getEagerLoads();
    expect($with)->toHaveKey('comments');
});

it('adds the paginate macro to Collection', function () {
    $collection = PostModel::all();
    $perPage = 10;

    $paginated = $collection->paginate($perPage);

    expect($paginated)->toBeInstanceOf(LengthAwarePaginator::class);
    expect($paginated->perPage())->toBe($perPage);
    expect($paginated->items())->toHaveCount($perPage);
});

it('initializer macro uses sortByDefaults when sortBy is not provided', function () {
    // Simulate a request without sortBy
    request()->merge([
        'filters' => json_encode(['title' => 'Sample']),
    ]);

    $query = PostModel::query()->initializer();

    // Assert that sortByDefaults are applied
    $orders = $query->getQuery()->orders;
    expect($orders)->toHaveCount(1);
    expect($orders[0]['column'])->toBe('created_at');
    expect($orders[0]['direction'])->toBe('desc');
});

it('initializer macro does not apply sorting when orderBy is false', function () {
    // Simulate a request with sorting
    request()->merge([
        'sortBy' => 'title',
        'descending' => false,
    ]);

    $query = PostModel::query()->initializer(orderBy: false);

    // Assert that no sorting is applied
    $orders = $query->getQuery()->orders;
    expect($orders)->toHaveCount(0);
});

it('initializer macro handles invalid JSON in filters gracefully', function () {
    // Simulate a request with invalid JSON in filters
    request()->merge([
        'filters' => '{invalid_json}',
    ]);

    $query = PostModel::query()->initializer();

    // Assert that no filters are applied
    expect($query->getQuery()->wheres)->toHaveCount(0);
});

it('initializer macro applies multiple filters correctly', function () {
    // Simulate a request with multiple filters
    request()->merge([
        'filters' => json_encode(['title' => 'Sample', 'body' => 'Test']),
    ]);

    $query = PostModel::query()->initializer();

    // Assert that multiple where clauses are applied
    expect($query->getQuery()->wheres)->toHaveCount(2);
});

it('likeWhere macro handles relational attributes correctly', function () {
    $query = PostModel::query()->likeWhere(['comments.comment'], 'Great');

    // Assert that whereHas is used for the relation
    $sql = $query->toSql();
    expect($sql)->toContain('exists');
});

it('paginates macro defaults to 15 per page when rowsPerPage is not set', function () {
    $query = PostModel::query()->paginates();

    expect($query->perPage())->toBe(15);
});

it('paginates macro defaults to 15 per page when rowsPerPage is zero', function () {
    // Simulate a request with 'rowsPerPage' as 0
    request()->merge(['rowsPerPage' => 0]);

    $query = PostModel::query()->paginates();

    expect($query->perPage())->toBe(15);
});

it('simplePaginates macro defaults to 15 per page when rowsPerPage is not set', function () {
    $query = PostModel::query()->simplePaginates();

    expect($query->perPage())->toBe(15);
});

it('simplePaginates macro defaults to 15 per page when rowsPerPage is zero', function () {
    // Simulate a request with 'rowsPerPage' as 0
    request()->merge(['rowsPerPage' => 0]);

    $query = PostModel::query()->simplePaginates();

    expect($query->perPage())->toBe(15);
});

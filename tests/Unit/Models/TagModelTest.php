<?php

use Anil\FastApiCrud\Tests\TestSetup\Models\PostModel;
use Anil\FastApiCrud\Tests\TestSetup\Models\TagModel;
use Illuminate\Support\Facades\Schema;

describe(description: 'Tag Model Class Test1', tests: function () {
    beforeEach(function () {
        $this->tagModel = new TagModel;
    });
    it(description: 'it should have correct fillable attributes', closure: function () {
        $fillableKeys = array_keys($this->tagModel->getFillable());
        sort($fillableKeys);
        $expectedKeys = array_keys([
            'name',
            'desc',
            'status',
            'active',
        ]);
        sort($expectedKeys);
        expect($fillableKeys)
            ->toBeArray()
            ->and($fillableKeys)
            ->toBe($expectedKeys);
    });
    it(description: 'it should have correct table name', closure: function () {
        expect($this->tagModel->getTable())
            ->toBe('tags');
    });
    it(description: 'it should have correct primary key', closure: function () {
        expect($this->tagModel->getKeyName())
            ->toBe('id');
    });

    it(description: 'it should have correct timestamps', closure: function () {
        expect($this->tagModel->getCreatedAtColumn())
            ->toBe('created_at')
            ->and($this->tagModel->getUpdatedAtColumn())
            ->toBe('updated_at');
    });
    it(description: 'it should have correct columns', closure: function () {
        $columns = Schema::getColumnListing($this->tagModel->getTable());
        sort($columns);
        $expectedColumns = [
            'active',
            'created_at',
            'deleted_at',
            'desc',
            'id',
            'name',
            'status',
            'updated_at',
        ];
        sort($expectedColumns);
        expect($columns)->toBe($expectedColumns);
    });

    it(description: 'should have all the method defined in the model', closure: function () {
        expect(method_exists($this->tagModel, 'afterCreateProcess'))->toBeTrue()
            ->and(method_exists($this->tagModel, 'afterUpdateProcess'))->toBeTrue()
            ->and(method_exists($this->tagModel, 'posts'))->toBeTrue()
            ->and(method_exists($this->tagModel, 'scopeQueryFilter'))->toBeTrue()
            ->and(method_exists($this->tagModel, 'scopeActive'))->toBeTrue()
            ->and(method_exists($this->tagModel, 'scopeStatus'))->toBeTrue();
    });

    it(description: 'should sync posts after creating a tag', closure: function () {
        $this->tagModel->name = 'Test Tag';
        $this->tagModel->desc = 'Test Description';
        $this->tagModel->status = 1;
        $this->tagModel->active = 1;
        $this->tagModel->save();

        $this->tagModel->afterCreateProcess();

        $posts = PostModel::factory(2)->create()->pluck('id')->toArray();
        // Assuming post_ids is passed in the request
        $this->tagModel->posts()->attach($posts); // Simulating post IDs
        expect($this->tagModel->posts()->count())->toBe(2);
    });

    it(description: 'should sync posts after updating a tag', closure: function () {
        $this->tagModel->name = 'Test Tag';
        $this->tagModel->desc = 'Test Description';
        $this->tagModel->status = 1;
        $this->tagModel->active = 1;
        $this->tagModel->save();

        $posts = PostModel::factory(2)->create()->pluck('id')->toArray();
        // Simulating post IDs
        $this->tagModel->posts()->attach($posts);
        $this->tagModel->afterUpdateProcess();

        $newPosts = PostModel::factory(7)->create()->pluck('id')->toArray();

        // Assuming post_ids is passed in the request
        $this->tagModel->posts()->sync($newPosts); // Updating post IDs
        expect($this->tagModel->posts()->count())->toBe(7);
    });
});

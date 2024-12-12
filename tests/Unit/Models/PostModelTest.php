<?php

use Anil\FastApiCrud\Tests\TestSetup\Models\PostModel;
use Anil\FastApiCrud\Tests\TestSetup\Models\TagModel;
use Anil\FastApiCrud\Tests\TestSetup\Models\UserModel;
use Illuminate\Support\Facades\Schema;

describe(description: 'Post Model Class Test', tests: function () {
    beforeEach(function () {
        $this->postModel = new PostModel;
    });

    it(description: 'it should have correct fillable attributes', closure: function () {
        $fillableKeys = array_keys($this->postModel->getFillable());
        sort($fillableKeys);
        $expectedKeys = array_keys([
            'name',
            'desc',
            'user_id',
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
        expect($this->postModel->getTable())
            ->toBe('posts');
    });

    it(description: 'it should have correct primary key', closure: function () {
        expect($this->postModel->getKeyName())
            ->toBe('id');
    });

    it(description: 'it should have correct timestamps', closure: function () {
        expect($this->postModel->getCreatedAtColumn())
            ->toBe('created_at')
            ->and($this->postModel->getUpdatedAtColumn())
            ->toBe('updated_at');
    });

    it(description: 'it should have correct columns', closure: function () {
        $columns = Schema::getColumnListing($this->postModel->getTable());
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
            'user_id',
        ];
        sort($expectedColumns);
        expect($columns)->toBe($expectedColumns);
    });

    it(description: 'should have all the method defined in the model', closure: function () {
        expect(method_exists($this->postModel, 'user'))->toBeTrue()
            ->and(method_exists($this->postModel, 'tags'))->toBeTrue();
    });

    it(description: 'should create a post and associate tags', closure: function () {
        $this->postModel->name = 'Test Post';
        $this->postModel->desc = 'Test Description';
        $this->postModel->status = 1;
        $this->postModel->active = 1;
        $this->postModel->user_id = UserModel::factory()->create()->id;
        $this->postModel->save();

        $tags = TagModel::factory(2)->create()->pluck('id')->toArray();
        $this->postModel->tags()->attach($tags);

        expect($this->postModel->tags()->count())->toBe(2);
    });

    it(description: 'should update a post and sync tags', closure: function () {
        $this->postModel->name = 'Test Post';
        $this->postModel->desc = 'Test Description';
        $this->postModel->status = 1;
        $this->postModel->active = 1;
        $this->postModel->user_id = UserModel::factory()->create()->id;
        $this->postModel->save();

        $tags = TagModel::factory(2)->create()->pluck('id')->toArray();
        $this->postModel->tags()->attach($tags);

        $newTags = TagModel::factory(3)->create()->pluck('id')->toArray();
        $this->postModel->tags()->sync($newTags);

        expect($this->postModel->tags()->count())->toBe(3);
    });

});

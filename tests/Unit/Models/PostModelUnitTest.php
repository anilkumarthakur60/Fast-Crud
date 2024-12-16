<?php

use Anil\FastApiCrud\Tests\TestSetup\Models\PostModel;
use Anil\FastApiCrud\Tests\TestSetup\Models\TagModel;
use Anil\FastApiCrud\Tests\TestSetup\Models\UserModel;
use Illuminate\Support\Facades\Schema;

describe(description: 'post_model_class_test', tests: function () {
    beforeEach(function () {
        $this->postModel = new PostModel();
    });

    it(description: 'it_should_have_correct_fillable_attributes', closure: function () {
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

    it(description: 'it_should_have_correct_table_name', closure: function () {
        expect($this->postModel->getTable())
            ->toBe('posts');
    });

    it(description: 'it_should_have_correct_primary_key', closure: function () {
        expect($this->postModel->getKeyName())
            ->toBe('id');
    });

    it(description: 'it_should_have_correct_timestamps', closure: function () {
        expect($this->postModel->getCreatedAtColumn())
            ->toBe('created_at')
            ->and($this->postModel->getUpdatedAtColumn())
            ->toBe('updated_at');
    });

    it(description: 'it_should_have_correct_columns', closure: function () {
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

    it(description: 'should_have_all_the_method_defined_in_the_model', closure: function () {
        expect(method_exists($this->postModel, 'user'))->toBeTrue()
            ->and(method_exists($this->postModel, 'tags'))->toBeTrue();
    });

    it(description: 'should_create_a_post_and_associate_tags', closure: function () {
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

    it(description: 'should_update_a_post_and_sync_tags', closure: function () {
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

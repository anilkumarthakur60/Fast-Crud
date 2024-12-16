<?php

use Anil\FastApiCrud\Tests\TestSetup\Models\PostModel;
use Anil\FastApiCrud\Tests\TestSetup\Models\UserModel;
use Illuminate\Support\Facades\Schema;

describe(description: 'user_model_class_unit_test', tests: function () {
    beforeEach(function () {
        $this->userModel = new UserModel();
    });

    it(description: 'it_should_have_correct_fillable_attributes', closure: function () {
        $fillableKeys = array_keys($this->userModel->getFillable());
        sort($fillableKeys);
        $expectedKeys = array_keys([
            'name',
            'email',
            'password',
            'status',
            'active',
        ]);
        sort($expectedKeys);
        expect($fillableKeys)
            ->toBeArray()
            ->and($fillableKeys)
            ->toBe($expectedKeys);
    });

    /** @test */
    it(description: 'it_should_have_correct_table_name', closure: function () {
        expect($this->userModel->getTable())
            ->toBe('users');
    });

    it(description: 'it_should_have_correct_primary_key', closure: function () {
        expect($this->userModel->getKeyName())
            ->toBe('id');
    });

    it(description: 'it_should_have_correct_timestamps', closure: function () {
        expect($this->userModel->getCreatedAtColumn())
            ->toBe('created_at')
            ->and($this->userModel->getUpdatedAtColumn())
            ->toBe('updated_at');
    });

    it(description: 'it_should_have_correct_columns', closure: function () {
        $columns = Schema::getColumnListing($this->userModel->getTable());
        sort($columns);
        $expectedColumns = [
            'active',
            'created_at',
            'deleted_at',
            'email',
            'id',
            'name',
            'password',
            'status',
            'updated_at',
        ];
        sort($expectedColumns);
        expect($columns)->toBe($expectedColumns);
    });

    it(description: 'should_have_all_the_method_defined_in_the_model', closure: function () {
        expect(method_exists($this->userModel, 'posts'))->toBeTrue();
    });

    it(description: 'should_create_a_user_and_associate_posts', closure: function () {
        $this->userModel->name = 'Test User';
        $this->userModel->email = 'test@example.com';
        $this->userModel->password = bcrypt('password');
        $this->userModel->status = 1;
        $this->userModel->active = 1;
        $this->userModel->save();

        $post = PostModel::factory()->create(['user_id' => $this->userModel->id]);

        expect($post->user_id)->toBe($this->userModel->id);
        expect($this->userModel->posts()->count())->toBe(1);
    });
});

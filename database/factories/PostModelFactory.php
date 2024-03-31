<?php

namespace Database\Factories;

use Anil\FastApiCrud\Tests\TestClasses\Models\PostModel;
use Anil\FastApiCrud\Tests\TestClasses\Models\UserModel;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostModelFactory extends Factory
{
    protected $model = PostModel::class;

    public function definition(): array
    {
        return [
            'name'        => $this->faker->name,
            'description' => $this->faker->text,
            'user_id'     => UserModel::factory(),
        ];
    }
}
<?php

namespace Anil\FastApiCrud\Database\Factories;

use Anil\FastApiCrud\Tests\TestClasses\Models\PostModel;
use Anil\FastApiCrud\Tests\TestClasses\Models\UserModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PostModel>
 */
class PostModelFactory extends Factory
{
    protected $model = PostModel::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'desc' => $this->faker->text,
            'user_id' => UserModel::factory(),
            'status' => $this->faker->boolean,
            'active' => $this->faker->boolean,
        ];
    }
}

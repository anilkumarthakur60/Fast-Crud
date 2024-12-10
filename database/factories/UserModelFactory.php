<?php

namespace Anil\FastApiCrud\Database\Factories;

use Anil\FastApiCrud\Tests\TestClasses\Models\UserModel;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserModelFactory extends Factory
{
    protected $model = UserModel::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail(),
            'password' => $this->faker->password,
            'status' => $this->faker->boolean,
            'active' => $this->faker->boolean,
        ];
    }
}

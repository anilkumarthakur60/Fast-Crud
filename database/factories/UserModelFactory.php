<?php

namespace Anil\FastApiCrud\Database\Factories;

use Anil\FastApiCrud\Tests\TestClasses\Models\UserModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserModel>
 */
class UserModelFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<UserModel>
     */
    protected $model = UserModel::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'     => $this->faker->name(),
            'email'    => $this->faker->unique()->safeEmail(),
            'password' => $this->faker->password(),
            'status'   => $this->faker->boolean(),
            'active'   => $this->faker->boolean(),
        ];
    }
}

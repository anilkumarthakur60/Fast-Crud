<?php

namespace Anil\FastApiCrud\Database\Factories;

use Anil\FastApiCrud\Tests\TestClasses\Models\TagModel;
use Illuminate\Database\Eloquent\Factories\Factory;

class TagModelFactory extends Factory
{

    protected $model = TagModel::class;
    
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->name,
        ];
    }
}

<?php

namespace Grafite\Database\Factories;

use Grafite\Cms\Models\Menu;
use Illuminate\Database\Eloquent\Factories\Factory;

class MenuFactory extends Factory
{
    protected $model = Menu::class;

    public function definition()
    {
        return [
            'id' => 1,
        'name' => 'dumb menu',
        'slug' => 'testerSLUG',
        'updated_at' => $this->faker->dateTime(),
        'created_at' => $this->faker->dateTime(),
        ];
    }
}

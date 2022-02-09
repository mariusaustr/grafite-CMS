<?php

namespace Grafite\Database\Factories;

use Grafite\Cms\Models\Widget;
use Illuminate\Database\Eloquent\Factories\Factory;

class WidgetFactory extends Factory
{
    protected $model = Widget::class;

    public function definition()
    {
        return [
            'id' => 1,
        'name' => 'test',
        'slug' => 'tester',
        'content' => implode(' ', $this->faker->paragraphs(3)),
        'updated_at' => $this->faker->dateTime(),
        'created_at' => $this->faker->dateTime(),
        ];
    }
}

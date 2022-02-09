<?php

namespace Grafite\Database\Factories;

use Grafite\Cms\Models\Blog;
use Illuminate\Database\Eloquent\Factories\Factory;

class BlogFactory extends Factory
{
    protected $model = Blog::class;

    public function definition()
    {
        return [
            'id' => 1,
        'title' => 'dumb',
        'entry' => $this->faker->paragraph().' '.$this->faker->paragraph(),
        'is_published' => 1,
        'url' => 'dumb',
        'updated_at' => $this->faker->dateTime(),
        'created_at' => $this->faker->dateTime(),
        ];
    }
}

<?php

namespace Grafite\Database\Factories;

use Grafite\Cms\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;

class PageFactory extends Factory
{
    protected $model = Page::class;

    public function definition()
    {
        return [
            'id' => 1,
        'title' => 'dumb',
        'url' => 'dumb',
        'seo_keywords' => 'dumb, dumber',
        'seo_description' => 'dumb is dumb',
        'entry' => $this->faker->paragraph().' '.$this->faker->paragraph(),
        'is_published' => 1,
        'updated_at' => $this->faker->dateTime(),
        'created_at' => $this->faker->dateTime(),
        ];
    }
}

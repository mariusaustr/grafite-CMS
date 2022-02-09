<?php

namespace Grafite\Database\Factories;

use Grafite\Cms\Models\Link;
use Illuminate\Database\Eloquent\Factories\Factory;

class LinkFactory extends Factory
{
    protected $model = Link::class;

    public function definition()
    {
        return [
            'id' => 1,
        'name' => 'dumb',
        'external' => 1,
        'page_id' => 0,
        'menu_id' => 1,
        'external_url' => 'http://facebook.com',
        'updated_at' => $this->faker->dateTime(),
        'created_at' => $this->faker->dateTime(),
        ];
    }
}

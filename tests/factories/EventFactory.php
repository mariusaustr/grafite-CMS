<?php

namespace Grafite\Database\Factories;

use Grafite\Cms\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition()
    {
        return [
            'id' => 1,
        'start_date' => '2016-10-31',
        'end_date' => '2016-10-31',
        'seo_keywords' => 'dumb, dumber',
        'seo_description' => 'dumb is dumb',
        'title' => 'dumb',
        'details' => $this->faker->paragraph().' '.$this->faker->paragraph(),
        'is_published' => 1,
        'updated_at' => $this->faker->dateTime(),
        'created_at' => $this->faker->dateTime(),
        ];
    }
}

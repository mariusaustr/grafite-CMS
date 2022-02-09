<?php

namespace Grafite\Database\Factories;

use Grafite\Cms\Models\Promotion;
use Illuminate\Database\Eloquent\Factories\Factory;

class PromotionFactory extends Factory
{
    protected $model = Promotion::class;

    public function definition()
    {
        return [
            'id' => 1,
        'published_at' => $this->faker->dateTime()->format('Y-m-d H:i'),
        'finished_at' => $this->faker->dateTime()->format('Y-m-d H:i'),
        'slug' => 'dumb',
        'details' => $this->faker->paragraph().' '.$this->faker->paragraph(),
        'updated_at' => $this->faker->dateTime(),
        'created_at' => $this->faker->dateTime(),
        ];
    }
}

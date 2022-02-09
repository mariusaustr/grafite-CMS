<?php

namespace Grafite\Database\Factories;

use Grafite\Cms\Models\Image;
use Illuminate\Database\Eloquent\Factories\Factory;

class ImageFactory extends Factory
{
    protected $model = Image::class;

    public function definition()
    {
        return [
            'id' => 1,
        'location' => 'files/dumb',
        'name' => 'dumb',
        'original_name' => 'dumb',
        'alt_tag' => 'dumb',
        'title_tag' => 'dumb',
        'is_published' => 1,
        'updated_at' => $this->faker->dateTime(),
        'created_at' => $this->faker->dateTime(),
        ];
    }
}

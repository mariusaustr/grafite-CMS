<?php

namespace Grafite\Database\Factories;

use Grafite\Cms\Models\File;
use Illuminate\Database\Eloquent\Factories\Factory;

class FileFactory extends Factory
{
    protected $model = File::class;

    public function definition()
    {
        return [
            'id' => 1,
        'location' => 'files/dumb',
        'name' => 'dumbFile',
        'tags' => 'dumb, file',
        'mime' => 'txt',
        'size' => 24,
        'details' => 'dumb file',
        'user' => 1,
        'is_published' => 1,
        'order' => 1,
        'updated_at' => $this->faker->dateTime(),
        'created_at' => $this->faker->dateTime(),
        ];
    }
}

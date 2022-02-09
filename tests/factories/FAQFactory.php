<?php

namespace Grafite\Database\Factories;

use Grafite\Cms\Models\FAQ;
use Illuminate\Database\Eloquent\Factories\Factory;

class FAQFactory extends Factory
{
    protected $model = FAQ::class;

    public function definition()
    {
        return [
            'id' => 1,
        'question' => 'what\'s this?',
        'answer' => 'There\'s color everywhere!',
        'is_published' => 1,
        'updated_at' => $this->faker->dateTime(),
        'created_at' => $this->faker->dateTime(),
        ];
    }
}

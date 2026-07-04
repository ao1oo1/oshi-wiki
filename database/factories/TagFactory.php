<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TagFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->word();

        return [
            'name' => $name,
            'slug' => Str::slug($name) ?: 'tag-' . fake()->unique()->numberBetween(1, 999999),
            'type' => 'general',
            'description' => null,
            'status' => 'published',
        ];
    }
}

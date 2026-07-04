<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class WorkFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->sentence(3);

        return [
            'title' => $title,
            'title_kana' => null,
            'slug' => Str::slug($title) ?: Str::random(12),
            'genre' => 'fantasy',
            'original_media' => 'novel',
            'official_url' => null,
            'guideline_url' => null,
            'description' => fake()->paragraph(),
            'status' => 'published',
            'review_status' => 'approved',
            'published_at' => now(),
        ];
    }
}

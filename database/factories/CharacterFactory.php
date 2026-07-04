<?php

namespace Database\Factories;

use App\Models\Work;
use Illuminate\Database\Eloquent\Factories\Factory;

class CharacterFactory extends Factory
{
    public function definition(): array
    {
        return [
            'work_id' => Work::factory(),
            'name' => fake()->name(),
            'name_kana' => null,
            'age' => null,
            'affiliation' => null,
            'grade_class' => null,
            'first_person' => null,
            'tone' => null,
            'tone_examples' => null,
            'personality' => fake()->paragraph(),
            'appearance' => null,
            'background' => null,
            'status' => 'draft',
        ];
    }
}

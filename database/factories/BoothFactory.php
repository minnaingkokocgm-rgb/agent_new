<?php

namespace Database\Factories;

use App\Models\Booth;
use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

class BoothFactory extends Factory
{
    protected $model = Booth::class;

    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'name' => fake()->unique()->words(3, true),
            'description' => fake()->paragraphs(2, true),
            'metadata' => [
                'products' => fake()->words(3),
                'staff_count' => fake()->numberBetween(1, 5),
                'demo_available' => fake()->boolean(),
            ],
        ];
    }
}

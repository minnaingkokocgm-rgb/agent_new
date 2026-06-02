<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\Summary;
use Illuminate\Database\Eloquent\Factories\Factory;

class SummaryFactory extends Factory
{
    protected $model = Summary::class;

    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'booth_id' => null,
            'visitor_id' => null,
            'content' => [
                'total_visitors' => fake()->numberBetween(10, 500),
                'key_themes' => fake()->words(4),
                'demographics' => [
                    'roles' => fake()->words(3),
                    'companies' => fake()->words(3),
                ],
                'sentiment' => fake()->randomElement(['positive', 'neutral', 'negative']),
                'actionable_insights' => fake()->sentences(3),
                'top_interests' => fake()->words(3),
                'recommendations' => fake()->paragraph(),
            ],
            'generated_at' => now(),
        ];
    }
}

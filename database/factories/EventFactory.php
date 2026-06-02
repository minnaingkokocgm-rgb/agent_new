<?php

namespace Database\Factories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->company().' '.fake()->randomElement(['Summit', 'Expo', 'Conference', 'Meetup']),
            'description' => fake()->paragraphs(3, true),
            'metadata' => [
                'location' => fake()->city(),
                'date' => fake()->dateTimeBetween('+1 week', '+6 months')->format('Y-m-d'),
                'expected_attendees' => fake()->numberBetween(50, 5000),
            ],
        ];
    }
}

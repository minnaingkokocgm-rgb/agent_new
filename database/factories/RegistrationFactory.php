<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\Registration;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Registration> */
class RegistrationFactory extends Factory
{
    protected $model = Registration::class;

    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->optional()->phoneNumber(),
            'company' => fake()->optional()->company(),
            'job_title' => fake()->optional()->jobTitle(),
            'country' => fake()->optional()->country(),
            'source' => fake()->optional()->randomElement(['social_media', 'email', 'referral', 'website']),
            'session_token' => (string) Str::uuid7(),
            'status' => 'pending',
        ];
    }

    public function reviewed(): static
    {
        return $this->state(fn () => ['status' => 'reviewed']);
    }

    public function approved(): static
    {
        return $this->state(fn () => ['status' => 'approved']);
    }
}

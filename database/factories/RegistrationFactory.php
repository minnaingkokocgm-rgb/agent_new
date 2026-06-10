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
            'post_code' => fake()->optional()->postcode(),
            'address' => fake()->optional()->address(),
            'organization' => fake()->optional()->company(),
            'occupation' => fake()->optional()->randomElement([
                'company_owner_executive', 'company_employee_government', 'sole_proprietor',
                'full_time_investor', 'corporate_investor', 'housewife_househusband',
                'retiree', 'student', 'other',
            ]),
            'age_range' => fake()->optional()->randomElement([
                'under_20', '20s', '30s', '40s', '50s', '60s', '70s_and_over',
            ]),
            'opt_out' => false,
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

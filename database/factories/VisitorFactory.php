<?php

namespace Database\Factories;

use App\Models\Visitor;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class VisitorFactory extends Factory
{
    protected $model = Visitor::class;

    public function definition(): array
    {
        return [
            'session_token' => (string) Str::uuid7(),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->optional()->phoneNumber(),
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
            'metadata' => [],
        ];
    }

    public function anonymous(): static
    {
        return $this->state(fn () => [
            'name' => null,
            'email' => null,
            'phone' => null,
        ]);
    }
}

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

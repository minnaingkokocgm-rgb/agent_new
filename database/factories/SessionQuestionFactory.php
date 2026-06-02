<?php

namespace Database\Factories;

use App\Models\SessionQuestion;
use App\Models\VisitorSession;
use Illuminate\Database\Eloquent\Factories\Factory;

class SessionQuestionFactory extends Factory
{
    protected $model = SessionQuestion::class;

    public function definition(): array
    {
        return [
            'session_id' => VisitorSession::factory(),
            'question_text' => fake()->sentence().'?',
            'question_order' => fake()->numberBetween(1, 5),
            'asked_at' => now(),
        ];
    }
}

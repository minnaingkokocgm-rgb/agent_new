<?php

namespace Database\Factories;

use App\Models\SessionAnswer;
use App\Models\SessionQuestion;
use App\Models\Visitor;
use App\Models\VisitorSession;
use Illuminate\Database\Eloquent\Factories\Factory;

class SessionAnswerFactory extends Factory
{
    protected $model = SessionAnswer::class;

    public function definition(): array
    {
        return [
            'question_id' => SessionQuestion::factory(),
            'session_id' => VisitorSession::factory(),
            'visitor_id' => Visitor::factory(),
            'answer_text' => fake()->sentences(2, true),
            'metadata' => null,
        ];
    }
}

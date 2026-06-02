<?php

namespace App\Actions;

use App\Ai\Agents\SurveyAgent;
use App\Models\SessionAnswer;
use App\Models\SessionQuestion;
use App\Models\VisitorSession;

class ProcessAnswer
{
    public function handle(VisitorSession $session, string $answer): array
    {
        $currentQuestion = $session->questions()
            ->whereDoesntHave('answer')
            ->orderBy('question_order')
            ->firstOrFail();

        SessionAnswer::create([
            'question_id' => $currentQuestion->id,
            'session_id' => $session->id,
            'visitor_id' => $session->visitor_id,
            'answer_text' => $answer,
        ]);

        $questionCount = $session->questions()->count();

        // After 5 questions, complete the session
        if ($questionCount >= 5) {
            $session->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            $agent = SurveyAgent::make($session->event, $session->booth)
                ->continueLastConversation($session->visitor);

            $response = $agent->prompt(
                "The visitor's final answer was: \"{$answer}\". "
                .'We have reached 5 questions. Thank the visitor warmly and end the conversation with [SURVEY_COMPLETE].'
            );

            return [
                'message' => $response->text,
                'status' => 'completed',
            ];
        }

        // Ask the next question
        $agent = SurveyAgent::make($session->event, $session->booth)
            ->continueLastConversation($session->visitor);

        $response = $agent->prompt(
            "The visitor answered: \"{$answer}\". Ask your next question."
        );

        $nextOrder = $questionCount + 1;
        $question = SessionQuestion::create([
            'session_id' => $session->id,
            'question_text' => $response->text,
            'question_order' => $nextOrder,
            'asked_at' => now(),
        ]);

        return [
            'message' => $response->text,
            'question_id' => $question->id,
            'status' => 'active',
        ];
    }
}

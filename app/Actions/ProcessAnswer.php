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

        // After 4 exchanges, complete the session
        if ($questionCount >= 4) {
            $session->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            $agent = SurveyAgent::make($session->event, $session->booth, $session->visitor->name ? $session->visitor : null)
                ->continueLastConversation($session->visitor);

            $response = $agent->prompt(
                "The visitor said: \"{$answer}\". "
                .'Wrap up the conversation warmly, offer a helpful pointer, and end with [SURVEY_COMPLETE].'
            );

            return [
                'message' => $response->text,
                'status' => 'completed',
            ];
        }

        // Continue the conversation naturally
        $agent = SurveyAgent::make($session->event, $session->booth, $session->visitor->name ? $session->visitor : null)
            ->continueLastConversation($session->visitor);

        $response = $agent->prompt(
            "The visitor said: \"{$answer}\". Respond naturally — answer any questions using the knowledge tool if needed, then continue the conversation."
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

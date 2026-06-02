<?php

namespace App\Actions;

use App\Ai\Agents\SurveyAgent;
use App\Models\Booth;
use App\Models\Event;
use App\Models\SessionQuestion;
use App\Models\Visitor;
use App\Models\VisitorSession;
use Illuminate\Support\Str;

class StartSurveySession
{
    public function handle(Event $event, ?Booth $booth = null): array
    {
        $visitor = Visitor::create([
            'session_token' => (string) Str::uuid7(),
        ]);

        $session = VisitorSession::create([
            'visitor_id' => $visitor->id,
            'event_id' => $event->id,
            'booth_id' => $booth?->id,
            'status' => 'active',
            'started_at' => now(),
        ]);

        $agent = SurveyAgent::make($event, $booth)
            ->forUser($visitor);

        $response = $agent->prompt(
            'Start the survey conversation. Greet the visitor warmly and ask your first question.'
        );

        $question = SessionQuestion::create([
            'session_id' => $session->id,
            'question_text' => $response->text,
            'question_order' => 1,
            'asked_at' => now(),
        ]);

        return [
            'session_id' => $session->id,
            'visitor_id' => $visitor->id,
            'message' => $response->text,
            'question_id' => $question->id,
            'status' => 'active',
        ];
    }
}

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
    public function handle(Event $event, ?Booth $booth = null, ?int $visitorId = null): array
    {
        if ($visitorId) {
            $visitor = Visitor::findOrFail($visitorId);
        } else {
            $visitor = Visitor::create([
                'session_token' => (string) Str::uuid7(),
            ]);
        }

        $session = VisitorSession::create([
            'visitor_id' => $visitor->id,
            'event_id' => $event->id,
            'booth_id' => $booth?->id,
            'status' => 'active',
            'started_at' => now(),
        ]);

        $agent = SurveyAgent::make($event, $booth, $visitor->name ? $visitor : null)
            ->forUser($visitor);

        $response = $agent->prompt(
            'A visitor just arrived. This is your FIRST and ONLY greeting. Give a brief, warm acknowledgment and ask one tailored opening question based on their profile. Do NOT use generic phrases like "Welcome to [event name]" — be natural and personal.'
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

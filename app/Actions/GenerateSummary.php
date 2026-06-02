<?php

namespace App\Actions;

use App\Ai\Agents\SummarizationAgent;
use App\Models\Booth;
use App\Models\Event;
use App\Models\Summary;
use App\Models\Visitor;

class GenerateSummary
{
    public function handle(Event $event, ?Booth $booth = null, ?Visitor $visitor = null): Summary
    {
        $context = $this->buildContext($event, $booth, $visitor);

        $agent = SummarizationAgent::make();

        $response = $agent->prompt(
            "Analyze and summarize the following survey data. Return ONLY a valid JSON object, no markdown, no explanation:\n\n{$context}"
        );

        $content = $this->parseJsonResponse($response->text);

        return Summary::create([
            'event_id' => $event->id,
            'booth_id' => $booth?->id,
            'visitor_id' => $visitor?->id,
            'content' => $content,
            'generated_at' => now(),
        ]);
    }

    private function parseJsonResponse(string $text): array
    {
        $text = trim($text);

        // Strip markdown code fences if present
        if (preg_match('/```(?:json)?\s*\n?(.+?)\n?```/s', $text, $m)) {
            $text = $m[1];
        }

        // Find the outermost { } block if there's extra text
        if (!str_starts_with($text, '{')) {
            if (preg_match('/\{.+\}/s', $text, $m)) {
                $text = $m[0];
            }
        }

        return json_decode($text, true, 512, JSON_THROW_ON_ERROR);
    }

    private function buildContext(Event $event, ?Booth $booth, ?Visitor $visitor): string
    {
        $query = $event->sessions()
            ->with(['questions.answer', 'visitor'])
            ->where('status', 'completed');

        if ($booth) {
            $query->where('booth_id', $booth->id);
        }

        if ($visitor) {
            $query->where('visitor_id', $visitor->id);
        }

        $sessions = $query->get();

        $lines = [
            "Event: {$event->name}",
            "Total completed sessions: {$sessions->count()}",
            '',
        ];

        foreach ($sessions as $session) {
            $visitorName = $session->visitor->name ?? 'Anonymous';
            $lines[] = "Visitor: {$visitorName}";

            foreach ($session->questions as $question) {
                $lines[] = "Q{$question->question_order}: {$question->question_text}";

                if ($question->answer) {
                    $lines[] = "A: {$question->answer->answer_text}";
                }
            }
            $lines[] = '';
        }

        return implode("\n", $lines);
    }
}

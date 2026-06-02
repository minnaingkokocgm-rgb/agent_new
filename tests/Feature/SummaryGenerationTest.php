<?php

use App\Ai\Agents\SummarizationAgent;
use App\Models\Event;
use App\Models\SessionAnswer;
use App\Models\SessionQuestion;
use App\Models\Visitor;
use App\Models\VisitorSession;

test('can generate event summary', function () {
    $event = Event::factory()->create(['name' => 'AI Summit 2026']);

    // Create completed sessions with questions and answers
    $visitor = Visitor::factory()->create(['name' => 'Alice']);
    $session = VisitorSession::factory()
        ->for($event)
        ->for($visitor, 'visitor')
        ->completed()
        ->create();

    $questions = [
        'What is your name?',
        'What brings you here?',
        'What are you interested in?',
    ];

    $answers = [
        'Alice, Engineer at TechCo',
        'Learning about AI',
        'Computer vision applications',
    ];

    foreach ($questions as $i => $q) {
        $question = SessionQuestion::factory()
            ->for($session, 'session')
            ->create([
                'question_text' => $q,
                'question_order' => $i + 1,
            ]);

        SessionAnswer::factory()
            ->for($question, 'question')
            ->for($session, 'session')
            ->for($visitor, 'visitor')
            ->create(['answer_text' => $answers[$i]]);
    }

    SummarizationAgent::fake([
        json_encode([
            'total_visitors' => 1,
            'key_themes' => ['AI', 'Computer Vision'],
            'demographics' => ['roles' => ['Engineer'], 'companies' => ['TechCo']],
            'sentiment' => 'positive',
            'actionable_insights' => ['Follow up with Alice'],
            'top_interests' => ['Computer vision applications'],
            'recommendations' => 'Schedule a demo.',
        ]),
    ]);

    $response = $this->getJson("/api/events/{$event->id}/summary");

    $response->assertOk()
        ->assertJsonPath('event_id', $event->id);

    $this->assertDatabaseCount('summaries', 1);
});

test('can regenerate event summary', function () {
    $event = Event::factory()->create();

    SummarizationAgent::fake([
        json_encode(['total_visitors' => 0, 'key_themes' => [], 'sentiment' => 'neutral', 'actionable_insights' => []]),
        json_encode(['total_visitors' => 0, 'key_themes' => [], 'sentiment' => 'neutral', 'actionable_insights' => []]),
    ]);

    // First summary
    $this->getJson("/api/events/{$event->id}/summary")->assertOk();
    expect($event->summaries()->count())->toBe(1);

    // Regenerate
    $this->postJson("/api/events/{$event->id}/summary/regenerate")->assertOk();
    expect($event->summaries()->count())->toBe(1); // old deleted, new created
});

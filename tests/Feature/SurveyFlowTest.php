<?php

use App\Ai\Agents\SurveyAgent;
use App\Models\Event;
use App\Models\VisitorSession;

test('complete survey flow stores 5 questions and answers', function () {
    $event = Event::factory()->create([
        'name' => 'TechConf 2026',
        'description' => 'A technology conference showcasing the latest innovations.',
    ]);

    SurveyAgent::fake([
        'Welcome to TechConf 2026! What is your name and what do you do?',
        'Great to meet you! What brings you to our event today?',
        'Interesting! What specific technologies are you most interested in?',
        'What is your timeline for implementing a solution?',
        'How can our team best follow up with you?',
        'Thanks so much for your time! We will reach out soon. [SURVEY_COMPLETE]',
    ]);

    // Start session
    $response = $this->postJson('/api/survey/start', [
        'event_id' => $event->id,
    ]);

    $response->assertOk()
        ->assertJsonPath('status', 'active')
        ->assertJsonPath('message', 'Welcome to TechConf 2026! What is your name and what do you do?');

    $sessionId = $response->json('session_id');

    // Answer 5 questions
    $answers = [
        'Jane Smith, CTO at Acme Corp',
        'Looking for AI solutions for our customer support',
        'NLP and conversational AI',
        'We are looking to implement within 3 months',
        'Email me at jane@acme.com',
    ];

    foreach ($answers as $i => $answer) {
        $status = $i === 4 ? 'completed' : 'active';
        $this->postJson("/api/survey/{$sessionId}/answer", [
            'answer' => $answer,
        ])->assertOk()
            ->assertJsonPath('status', $status);
    }

    // Verify database state
    $this->assertDatabaseCount('session_questions', 5);
    $this->assertDatabaseCount('session_answers', 5);

    $session = VisitorSession::find($sessionId);
    expect($session->status)->toBe('completed');
    expect($session->completed_at)->not->toBeNull();
});

test('survey start requires valid event', function () {
    $this->postJson('/api/survey/start', [
        'event_id' => 9999,
    ])->assertStatus(422);
});

test('cannot answer completed session', function () {
    $event = Event::factory()->create();

    SurveyAgent::fake([
        'Hello! What is your name?',
    ]);

    $response = $this->postJson('/api/survey/start', [
        'event_id' => $event->id,
    ]);
    $sessionId = $response->json('session_id');

    // Manually complete the session
    $this->postJson("/api/survey/{$sessionId}/complete")->assertOk();

    // Attempting to answer should fail
    $this->postJson("/api/survey/{$sessionId}/answer", [
        'answer' => 'My name is John',
    ])->assertStatus(422);
});

test('manually complete an active session', function () {
    $event = Event::factory()->create();

    SurveyAgent::fake([
        'Hello! What is your name?',
    ]);

    $response = $this->postJson('/api/survey/start', [
        'event_id' => $event->id,
    ]);
    $sessionId = $response->json('session_id');

    $this->postJson("/api/survey/{$sessionId}/complete")
        ->assertOk()
        ->assertJsonPath('status', 'completed');

    $this->assertDatabaseHas('visitor_sessions', [
        'id' => $sessionId,
        'status' => 'completed',
    ]);
});

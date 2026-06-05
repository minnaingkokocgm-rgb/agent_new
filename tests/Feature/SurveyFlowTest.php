<?php

use App\Ai\Agents\SurveyAgent;
use App\Models\Event;
use App\Models\VisitorSession;

test('complete survey flow stores 4 questions and answers', function () {
    $event = Event::factory()->create([
        'name' => 'TechConf 2026',
        'description' => 'A technology conference showcasing the latest innovations.',
    ]);

    SurveyAgent::fake([
        'Welcome to TechConf 2026! We have 5 tracks including AI and Cloud. What brings you here today?',
        'Great choice! Our AI track features hands-on workshops. What specific technologies interest you most?',
        'We have several NLP and computer vision demos at the AI booth. What is your timeline for exploring solutions?',
        'Perfect — I recommend visiting the AI Demo Station and Cloud Infrastructure booth. How can we best follow up?',
        'Thanks so much for chatting! I will send you the info on those booths. Enjoy the conference! [SURVEY_COMPLETE]',
    ]);

    // Start session
    $response = $this->postJson('/api/survey/start', [
        'event_id' => $event->id,
    ]);

    $response->assertOk()
        ->assertJsonPath('status', 'active')
        ->assertJsonPath('message', 'Welcome to TechConf 2026! We have 5 tracks including AI and Cloud. What brings you here today?');

    $sessionId = $response->json('session_id');

    // Answer 4 questions (completes on the 4th)
    $answers = [
        'Looking for AI solutions for our customer support team',
        'NLP and conversational AI are our top priorities',
        'We want to implement within the next 3 months',
        'Email me at jane@acme.com and send booth info',
    ];

    foreach ($answers as $i => $answer) {
        $status = $i === 3 ? 'completed' : 'active';
        $this->postJson("/api/survey/{$sessionId}/answer", [
            'answer' => $answer,
        ])->assertOk()
            ->assertJsonPath('status', $status);
    }

    // Verify database state
    $this->assertDatabaseCount('session_questions', 4);
    $this->assertDatabaseCount('session_answers', 4);

    $session = VisitorSession::find($sessionId);
    expect($session->status)->toBe('completed');
    expect($session->completed_at)->not->toBeNull();
});

test('survey page shows four question maximum', function () {
    $event = Event::factory()->create();

    $this->get(route('survey.chat', $event))
        ->assertOk()
        ->assertSee('Question 1 of 4')
        ->assertDontSee('Question 1 of 5');
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

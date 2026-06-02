<?php

use App\Ai\Agents\RegistrationAssistantAgent;
use App\Models\Event;
use App\Models\Registration;

test('registration chat can be started', function () {
    $event = Event::factory()->create([
        'name' => 'AI Summit 2026',
        'description' => 'An AI conference.',
    ]);

    RegistrationAssistantAgent::fake([
        'Hello! Welcome to the registration form. How can I help you today?',
    ]);

    $response = $this->postJson('/api/registration/start', [
        'event_id' => $event->id,
    ]);

    $response->assertOk()
        ->assertJsonStructure(['session_token', 'message'])
        ->assertJsonPath('message', 'Hello! Welcome to the registration form. How can I help you today?');

    $this->assertDatabaseHas('registrations', [
        'event_id' => $event->id,
        'status' => 'pending',
        'session_token' => $response->json('session_token'),
    ]);
});

test('registration chat can ask questions', function () {
    $event = Event::factory()->create();

    RegistrationAssistantAgent::fake([
        'Welcome to registration!',
        'The Company field is for your organization name.',
    ]);

    // Start chat
    $start = $this->postJson('/api/registration/start', [
        'event_id' => $event->id,
    ])->assertOk();

    $token = $start->json('session_token');

    // Ask a question
    $ask = $this->postJson('/api/registration/ask', [
        'session_token' => $token,
        'question' => 'What should I put in the Company field?',
    ]);

    $ask->assertOk()
        ->assertJsonStructure(['message']);
});

test('registration form can be submitted', function () {
    $event = Event::factory()->create();

    RegistrationAssistantAgent::fake(['Welcome!']);

    // Start chat first
    $start = $this->postJson('/api/registration/start', [
        'event_id' => $event->id,
    ]);

    $token = $start->json('session_token');

    // Submit form
    $response = $this->postJson('/api/registration/submit', [
        'session_token' => $token,
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'phone' => '+1-555-123-4567',
        'company' => 'Acme Corp',
        'job_title' => 'Software Engineer',
        'country' => 'US',
        'source' => 'website',
    ]);

    $response->assertOk()
        ->assertJsonPath('status', 'submitted');

    $this->assertDatabaseHas('registrations', [
        'session_token' => $token,
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'status' => 'submitted',
    ]);
});

test('registration start requires event_id', function () {
    $this->postJson('/api/registration/start', [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['event_id']);
});

test('registration ask requires session_token', function () {
    $this->postJson('/api/registration/ask', [
        'question' => 'Hello?',
    ])->assertStatus(422)
        ->assertJsonValidationErrors(['session_token']);
});

test('registration submit requires name and email', function () {
    $event = Event::factory()->create();

    RegistrationAssistantAgent::fake(['Welcome!']);

    $start = $this->postJson('/api/registration/start', ['event_id' => $event->id]);
    $token = $start->json('session_token');

    $this->postJson('/api/registration/submit', [
        'session_token' => $token,
    ])->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'email']);
});

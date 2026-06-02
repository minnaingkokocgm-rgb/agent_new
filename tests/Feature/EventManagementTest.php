<?php

use App\Models\Event;

test('can create an event', function () {
    $response = $this->postJson('/api/events', [
        'name' => 'AI Summit 2026',
        'description' => 'The premier AI conference of the year.',
        'metadata' => [
            'location' => 'San Francisco',
            'date' => '2026-09-15',
        ],
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('name', 'AI Summit 2026')
        ->assertJsonPath('metadata.location', 'San Francisco');

    $this->assertDatabaseHas('events', ['name' => 'AI Summit 2026']);
});

test('can list events', function () {
    Event::factory()->count(3)->create();

    $response = $this->getJson('/api/events');

    $response->assertOk()
        ->assertJsonCount(3);
});

test('can create a booth for an event', function () {
    $event = Event::factory()->create();

    $response = $this->postJson("/api/events/{$event->id}/booths", [
        'name' => 'NLP Demo Station',
        'description' => 'Live demo of our NLP pipeline.',
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('name', 'NLP Demo Station')
        ->assertJsonPath('event_id', $event->id);

    $this->assertDatabaseHas('booths', [
        'event_id' => $event->id,
        'name' => 'NLP Demo Station',
    ]);
});

test('can delete an event and its booths cascade', function () {
    $event = Event::factory()->create();
    $event->booths()->create([
        'name' => 'Demo Booth',
        'description' => 'A demo booth.',
    ]);

    $this->assertDatabaseCount('booths', 1);

    $this->deleteJson("/api/events/{$event->id}")->assertStatus(204);

    $this->assertDatabaseCount('events', 0);
    $this->assertDatabaseCount('booths', 0);
});

test('event creation validates required fields', function () {
    $this->postJson('/api/events', [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'description']);
});

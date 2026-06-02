<?php

use App\Models\Event;
use App\Models\KnowledgeChunk;
use Laravel\Ai\Embeddings;

test('knowledge indexing creates vector chunks', function () {
    $event = Event::factory()->create();

    $content = str_repeat('This is a test document about cutting-edge AI technology for enterprise customers. ', 100);

    Embeddings::fake();

    $response = $this->postJson("/api/events/{$event->id}/knowledge", [
        'content' => $content,
        'source_name' => 'test-doc.txt',
    ]);

    $response->assertOk()
        ->assertJsonPath('message', 'Knowledge indexed successfully.')
        ->assertJsonPath('chunks_created', fn ($val) => $val > 0);

    expect(KnowledgeChunk::count())->toBeGreaterThan(0);
});

test('knowledge indexing requires content', function () {
    $event = Event::factory()->create();

    $this->postJson("/api/events/{$event->id}/knowledge", [
        'content' => '',
        'source_name' => 'test.txt',
    ])->assertStatus(422);
});

test('knowledge indexing scopes to booth when provided', function () {
    $event = Event::factory()->create();
    $booth = $event->booths()->create([
        'name' => 'AI Demo Booth',
        'description' => 'Showcasing AI solutions.',
    ]);

    $content = str_repeat('Booth-specific AI content for demo purposes. ', 50);

    Embeddings::fake();

    $response = $this->postJson("/api/events/{$event->id}/knowledge", [
        'content' => $content,
        'source_name' => 'booth-info.txt',
        'booth_id' => $booth->id,
    ]);

    $response->assertOk();

    $this->assertDatabaseHas('knowledge_chunks', [
        'event_id' => $event->id,
        'booth_id' => $booth->id,
    ]);
});

<?php

use App\Ai\Tools\EventKnowledgeSearch;
use App\Models\Event;
use App\Models\KnowledgeChunk;
use Laravel\Ai\Embeddings;
use Laravel\Ai\Tools\Request;

beforeEach(function () {
    $this->event = Event::factory()->create([
        'name' => 'AI Summit',
        'description' => 'The premier AI conference.',
    ]);
});

test('event knowledge search handles empty knowledge base', function () {
    Embeddings::fake([
        [Embeddings::fakeEmbedding(1536)],
    ]);

    $tool = new EventKnowledgeSearch($this->event, null);

    $result = $tool->handle(new Request(['query' => 'What AI products do you have?']));

    expect($result)->toBeString()
        ->toContain('No relevant information found');
});

test('event knowledge search executes without errors when chunks exist', function () {
    Embeddings::fake([
        [Embeddings::fakeEmbedding(1536)],
    ]);

    KnowledgeChunk::factory()
        ->forEvent($this->event)
        ->withEmbedding(Embeddings::fakeEmbedding(1536))
        ->create([
            'chunk_text' => 'Our booth showcases NLP and computer vision products.',
            'chunk_order' => 0,
        ]);

    $tool = new EventKnowledgeSearch($this->event, null);

    $result = $tool->handle(new Request(['query' => 'What AI products do you have?']));

    // Should not throw; result may or may not contain matches with fake embeddings
    expect($result)->toBeString();
});

test('event knowledge search has description and schema', function () {
    $tool = new EventKnowledgeSearch($this->event, null);

    expect($tool->description())->toBeString()->not->toBeEmpty();
});

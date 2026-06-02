<?php

namespace Database\Factories;

use App\Models\Booth;
use App\Models\Event;
use App\Models\KnowledgeChunk;
use Illuminate\Database\Eloquent\Factories\Factory;
use Laravel\Ai\Embeddings;

class KnowledgeChunkFactory extends Factory
{
    protected $model = KnowledgeChunk::class;

    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'booth_id' => null,
            'chunk_text' => fake()->paragraphs(3, true),
            'embedding' => Embeddings::fakeEmbedding(1536),
            'chunk_order' => fake()->numberBetween(0, 10),
            'metadata' => [
                'source' => fake()->word().'.txt',
                'chunked_at' => now()->toIsoString(),
            ],
        ];
    }

    public function forEvent(Event $event): static
    {
        return $this->state(fn () => ['event_id' => $event->id]);
    }

    public function forBooth(Booth $booth): static
    {
        return $this->state(fn () => [
            'event_id' => $booth->event_id,
            'booth_id' => $booth->id,
        ]);
    }

    public function withEmbedding(array $embedding): static
    {
        return $this->state(fn () => ['embedding' => $embedding]);
    }
}

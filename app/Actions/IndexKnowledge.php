<?php

namespace App\Actions;

use App\Ai\Tools\EventKnowledgeSearch;
use App\Models\Booth;
use App\Models\Event;
use App\Models\KnowledgeChunk;
use Laravel\Ai\Embeddings;

class IndexKnowledge
{
    public function handle(Event $event, ?Booth $booth, string $content, string $sourceName): int
    {
        $chunks = $this->chunkText($content, 500, 50);

        if (empty($chunks)) {
            return 0;
        }

        $embeddingResponse = Embeddings::for($chunks)
            ->dimensions(1536)
            ->cache()
            ->generate(provider: 'openrouter', model: 'openai/text-embedding-3-small');

        $inserted = 0;
        foreach ($chunks as $i => $chunkText) {
            KnowledgeChunk::create([
                'event_id' => $event->id,
                'booth_id' => $booth?->id,
                'chunk_text' => $chunkText,
                'embedding' => $embeddingResponse->embeddings[$i],
                'chunk_order' => $i,
                'metadata' => [
                    'source' => $sourceName,
                    'chunked_at' => now()->toIsoString(),
                ],
            ]);
            $inserted++;
        }

        // Clear knowledge search cache for this event/booth
        EventKnowledgeSearch::clearCache($event, $booth);

        return $inserted;
    }

    /** @return string[] */
    private function chunkText(string $text, int $wordsPerChunk, int $overlapWords): array
    {
        $words = preg_split('/\s+/', trim($text));
        $chunks = [];

        for ($i = 0; $i < count($words); $i += ($wordsPerChunk - $overlapWords)) {
            $chunk = array_slice($words, $i, $wordsPerChunk);
            if (count($chunk) < 10) {
                break;
            }
            $chunks[] = implode(' ', $chunk);
        }

        return $chunks;
    }
}

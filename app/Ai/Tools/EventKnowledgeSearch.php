<?php

namespace App\Ai\Tools;

use App\Models\Booth;
use App\Models\Event;
use App\Models\KnowledgeChunk;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Embeddings;
use Laravel\Ai\Tools\Request;
use Stringable;

class EventKnowledgeSearch implements Tool
{
    protected string $description = 'Search event and booth knowledge base for relevant information. Use this when you need to reference specific event details, booth products, or answer visitor questions about the event.';

    public function __construct(
        private Event $event,
        private ?Booth $booth = null,
    ) {}

    public function description(): Stringable|string
    {
        return $this->description;
    }

    public function handle(Request $request): Stringable|string
    {
        $query = $request->string('query')->value();

        // Generate embedding for the search query
        $embeddingResponse = Embeddings::for([$query])
            ->dimensions(1536)
            ->generate(provider: 'openrouter', model: 'openai/text-embedding-3-small');

        $queryEmbedding = $embeddingResponse->embeddings[0];

        // Search knowledge_chunks scoped to this event (and booth if applicable)
        $results = KnowledgeChunk::query()
            ->where('event_id', $this->event->id)
            ->when($this->booth, fn ($q) => $q->where('booth_id', $this->booth->id))
            ->whereVectorSimilarTo('embedding', $queryEmbedding, 0.6)
            ->limit(5)
            ->get();

        if ($results->isEmpty()) {
            return 'No relevant information found in the knowledge base.';
        }

        return "Relevant event information found:\n\n"
            .$results->pluck('chunk_text')->implode("\n\n---\n\n");
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'query' => $schema
                ->string()
                ->description('The search query to find relevant event or booth information.')
                ->required(),
        ];
    }
}

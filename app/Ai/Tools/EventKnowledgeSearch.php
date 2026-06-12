<?php

namespace App\Ai\Tools;

use App\Models\Booth;
use App\Models\Event;
use App\Models\KnowledgeChunk;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Cache;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Embeddings;
use Laravel\Ai\Tools\Request;
use Stringable;

class EventKnowledgeSearch implements Tool
{
    private const MinimumSimilarity = 0.1;

    private const CacheTtl = 3600; // 1 hour

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
        $cacheKey = $this->buildCacheKey($query);

        // Check cache first
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        // Generate embedding for the search query
        $embeddingResponse = Embeddings::for([$query])
            ->dimensions(1536)
            ->generate(provider: 'openrouter', model: 'openai/text-embedding-3-small');

        $queryEmbedding = $embeddingResponse->embeddings[0];

        // Search knowledge_chunks scoped to this event.
        // When booth is set: include both booth-specific AND event-wide chunks.
        $results = KnowledgeChunk::query()
            ->where('event_id', $this->event->id)
            ->when($this->booth, fn ($q) => $q->where(function ($q) {
                $q->where('booth_id', $this->booth->id)
                    ->orWhereNull('booth_id');
            }))
            ->whereVectorSimilarTo('embedding', $queryEmbedding, self::MinimumSimilarity)
            ->limit(5)
            ->get();

        if ($results->isEmpty()) {
            $response = 'No relevant information found in the knowledge base.';
        } else {
            $response = "Relevant event information found:\n\n"
                .$results->pluck('chunk_text')->implode("\n\n---\n\n");
        }

        // Cache the result
        Cache::put($cacheKey, $response, self::CacheTtl);

        return $response;
    }

    /**
     * Build a cache key for the search query.
     */
    private function buildCacheKey(string $query): string
    {
        $normalizedQuery = $this->normalizeQuery($query);
        $queryHash = md5($normalizedQuery);
        $boothId = $this->booth?->id ?? 'none';
        $version = $this->getCacheVersion();

        return "knowledge_search:{$this->event->id}:{$boothId}:v{$version}:{$queryHash}";
    }

    /**
     * Get the current cache version for this event/booth.
     */
    private function getCacheVersion(): int
    {
        $boothId = $this->booth?->id ?? 'none';

        return (int) Cache::get("knowledge_version:{$this->event->id}:{$boothId}", 1);
    }

    /**
     * Normalize query for better cache hits.
     */
    private function normalizeQuery(string $query): string
    {
        return trim(preg_replace('/\s+/', ' ', strtolower($query)));
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

    /**
     * Clear cache for this event (or specific booth) by incrementing version.
     */
    public static function clearCache(Event $event, ?Booth $booth = null): void
    {
        $boothId = $booth?->id ?? 'none';
        $versionKey = "knowledge_version:{$event->id}:{$boothId}";
        $currentVersion = (int) Cache::get($versionKey, 1);
        Cache::put($versionKey, $currentVersion + 1, self::CacheTtl * 2);
    }
}

<?php

namespace App\Models;

use Database\Factories\KnowledgeChunkFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Pgvector\Laravel\HasNeighbors;
use Pgvector\Laravel\Vector;

#[Fillable(['event_id', 'booth_id', 'chunk_text', 'embedding', 'chunk_order', 'metadata'])]
class KnowledgeChunk extends Model
{
    /** @use HasFactory<KnowledgeChunkFactory> */
    use HasFactory;

    use HasNeighbors;

    protected function casts(): array
    {
        return [
            'embedding' => Vector::class,
            'metadata' => 'array',
        ];
    }

    /** @return BelongsTo<Event, $this> */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /** @return BelongsTo<Booth, $this> */
    public function booth(): BelongsTo
    {
        return $this->belongsTo(Booth::class);
    }
}

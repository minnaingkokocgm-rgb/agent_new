<?php

namespace App\Models;

use Database\Factories\BoothFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['event_id', 'name', 'description', 'metadata'])]
class Booth extends Model
{
    /** @use HasFactory<BoothFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    /** @return BelongsTo<Event, $this> */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /** @return HasMany<VisitorSession, $this> */
    public function sessions(): HasMany
    {
        return $this->hasMany(VisitorSession::class);
    }

    /** @return HasMany<KnowledgeChunk, $this> */
    public function knowledgeChunks(): HasMany
    {
        return $this->hasMany(KnowledgeChunk::class);
    }

    /** @return HasMany<Summary, $this> */
    public function summaries(): HasMany
    {
        return $this->hasMany(Summary::class);
    }
}

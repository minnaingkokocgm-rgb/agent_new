<?php

namespace App\Models;

use Database\Factories\EventFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'description', 'metadata'])]
class Event extends Model
{
    /** @use HasFactory<EventFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    /** @return HasMany<Booth, $this> */
    public function booths(): HasMany
    {
        return $this->hasMany(Booth::class);
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

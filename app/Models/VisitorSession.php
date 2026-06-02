<?php

namespace App\Models;

use Database\Factories\VisitorSessionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['visitor_id', 'event_id', 'booth_id', 'status', 'started_at', 'completed_at'])]
class VisitorSession extends Model
{
    /** @use HasFactory<VisitorSessionFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Visitor, $this> */
    public function visitor(): BelongsTo
    {
        return $this->belongsTo(Visitor::class);
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

    /** @return HasMany<SessionQuestion, $this> */
    public function questions(): HasMany
    {
        return $this->hasMany(SessionQuestion::class, 'session_id');
    }

    /** @return HasMany<SessionAnswer, $this> */
    public function answers(): HasMany
    {
        return $this->hasMany(SessionAnswer::class, 'session_id');
    }
}

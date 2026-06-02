<?php

namespace App\Models;

use Database\Factories\SummaryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['event_id', 'booth_id', 'visitor_id', 'content', 'generated_at'])]
class Summary extends Model
{
    /** @use HasFactory<SummaryFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'content' => 'array',
            'generated_at' => 'datetime',
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

    /** @return BelongsTo<Visitor, $this> */
    public function visitor(): BelongsTo
    {
        return $this->belongsTo(Visitor::class);
    }
}

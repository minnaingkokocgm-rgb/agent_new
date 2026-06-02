<?php

namespace App\Models;

use Database\Factories\SessionAnswerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['question_id', 'session_id', 'visitor_id', 'answer_text', 'metadata'])]
class SessionAnswer extends Model
{
    /** @use HasFactory<SessionAnswerFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    /** @return BelongsTo<SessionQuestion, $this> */
    public function question(): BelongsTo
    {
        return $this->belongsTo(SessionQuestion::class, 'question_id');
    }

    /** @return BelongsTo<VisitorSession, $this> */
    public function session(): BelongsTo
    {
        return $this->belongsTo(VisitorSession::class, 'session_id');
    }

    /** @return BelongsTo<Visitor, $this> */
    public function visitor(): BelongsTo
    {
        return $this->belongsTo(Visitor::class);
    }
}

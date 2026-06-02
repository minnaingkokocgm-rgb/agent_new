<?php

namespace App\Models;

use Database\Factories\SessionQuestionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['session_id', 'question_text', 'question_order', 'asked_at'])]
class SessionQuestion extends Model
{
    /** @use HasFactory<SessionQuestionFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'asked_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<VisitorSession, $this> */
    public function session(): BelongsTo
    {
        return $this->belongsTo(VisitorSession::class, 'session_id');
    }

    /** @return HasOne<SessionAnswer, $this> */
    public function answer(): HasOne
    {
        return $this->hasOne(SessionAnswer::class, 'question_id');
    }
}

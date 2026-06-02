<?php

namespace App\Models;

use Database\Factories\VisitorFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['session_token', 'name', 'email', 'phone', 'metadata'])]
class Visitor extends Model
{
    /** @use HasFactory<VisitorFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    /** @return HasMany<VisitorSession, $this> */
    public function sessions(): HasMany
    {
        return $this->hasMany(VisitorSession::class);
    }

    /** @return HasMany<SessionAnswer, $this> */
    public function answers(): HasMany
    {
        return $this->hasMany(SessionAnswer::class);
    }

    /** @return HasMany<Summary, $this> */
    public function summaries(): HasMany
    {
        return $this->hasMany(Summary::class);
    }
}

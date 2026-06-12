<?php

namespace App\Models;

use Database\Factories\RegistrationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'event_id', 'visitor_id', 'name', 'email', 'password', 'phone', 'company',
    'industry', 'department', 'post', 'post_code', 'address', 'opt_out',
    'reception_category', 'responsible_organization',
    'document_path', 'session_token', 'status', 'metadata',
])]
class Registration extends Model
{
    /** @use HasFactory<RegistrationFactory> */
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

    /** @return BelongsTo<Visitor, $this> */
    public function visitor(): BelongsTo
    {
        return $this->belongsTo(Visitor::class);
    }
}

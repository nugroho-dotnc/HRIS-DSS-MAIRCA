<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $fillable = [
        'type',
        'title',
        'body',
        'data',
        'recipient_type',
        'recipient_id',
        'is_read',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'data'    => 'array',
            'is_read' => 'boolean',
            'sent_at' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Scope: filter notifikasi untuk recipient tertentu
     */
    public function scopeForRecipient(Builder $query, string $recipientType, int $recipientId): Builder
    {
        return $query->where('recipient_type', $recipientType)
                     ->where('recipient_id', $recipientId);
    }

    /**
     * Scope: hanya notifikasi yang belum dibaca
     */
    public function scopeUnread(Builder $query): Builder
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope: hanya notifikasi untuk role HR
     */
    public function scopeForHr(Builder $query): Builder
    {
        return $query->where('recipient_type', 'hr');
    }

    /**
     * Scope: hanya notifikasi untuk role Candidate
     */
    public function scopeForCandidate(Builder $query): Builder
    {
        return $query->where('recipient_type', 'candidate');
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Relasi ke User (hanya berlaku jika recipient_type = 'hr')
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id', 'id');
    }

    /**
     * Relasi ke Application (hanya berlaku jika recipient_type = 'candidate')
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class, 'recipient_id', 'id');
    }
}

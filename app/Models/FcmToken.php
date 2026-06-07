<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FcmToken extends Model
{
    protected $fillable = [
        'owner_type',
        'owner_id',
        'fcm_token',
    ];

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Scope: hanya token milik HR (owner_type = 'hr', owner_id = user_id)
     */
    public function scopeForHr(Builder $query): Builder
    {
        return $query->where('owner_type', 'hr');
    }

    /**
     * Scope: hanya token milik candidate (owner_type = 'candidate', owner_id = application_id)
     */
    public function scopeForCandidate(Builder $query): Builder
    {
        return $query->where('owner_type', 'candidate');
    }

    /**
     * Scope: filter by specific owner
     */
    public function scopeForOwner(Builder $query, string $ownerType, int $ownerId): Builder
    {
        return $query->where('owner_type', $ownerType)->where('owner_id', $ownerId);
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Relasi ke User (hanya berlaku jika owner_type = 'hr')
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id', 'id');
    }

    /**
     * Relasi ke Application (hanya berlaku jika owner_type = 'candidate')
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class, 'owner_id', 'id');
    }
}

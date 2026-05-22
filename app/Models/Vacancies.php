<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vacancies extends Model
{
    protected $fillable = [
        'hr_id',
        'position_id',
        'title',
        'description',
        'requirements',
        'deadline',
        'status',
    ];

    public function hr(): BelongsTo
    {
        return $this->belongsTo(User::class, 'hr_id', 'id');
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'position_id', 'id');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class, 'vacancy_id', 'id');
    }
}

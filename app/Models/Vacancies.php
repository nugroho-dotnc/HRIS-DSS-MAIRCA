<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vacancies extends Model
{
    protected $fillable = ['hr_id', 'position_id', 'title', 'description', 'requirements', 'deadline', 'status'];

    public function Hr(): BelongsTo{
        return $this->belongsTo(User::class, 'hr_id', 'id');
    }
    public function Position(): BelongsTo{
        return $this->belongsTo(Position::class, 'position_id', 'id');
    }
}

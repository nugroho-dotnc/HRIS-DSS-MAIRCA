<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InterviewScore extends Model
{
    protected $fillable = [
        'session_id',
        'criteria_id',
        'score',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(InterviewSession::class, 'session_id', 'id');
    }

    public function criteria(): BelongsTo
    {
        return $this->belongsTo(RecruitmentCriteria::class, 'criteria_id', 'id');
    }
}

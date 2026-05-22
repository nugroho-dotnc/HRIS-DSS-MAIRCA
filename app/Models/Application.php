<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Application extends Model
{
    protected $fillable = [
        'candidate_id',
        'vacancy_id',
        'status',
        'application_code',
    ];

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class, 'candidate_id', 'id');
    }

    public function vacancy(): BelongsTo
    {
        return $this->belongsTo(Vacancies::class, 'vacancy_id', 'id');
    }

    public function interviewSessions(): HasMany
    {
        return $this->hasMany(InterviewSession::class, 'application_id', 'id');
    }

    public function result(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(RecruitmentResult::class, 'application_id', 'id');
    }
}

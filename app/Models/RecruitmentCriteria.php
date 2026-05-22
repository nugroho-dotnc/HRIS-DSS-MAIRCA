<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RecruitmentCriteria extends Model
{
    protected $table = 'recruitment_criterias';

    protected $fillable = [
        'position_id',
        'name',
        'weight',
        'description',
        'type',
        'data_type',
    ];

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'position_id', 'id');
    }

    public function likertScales(): HasMany
    {
        return $this->hasMany(LikertScale::class, 'recruitment_criterias_id', 'id');
    }

    public function interviewScores(): HasMany
    {
        return $this->hasMany(InterviewScore::class, 'criteria_id', 'id');
    }
}

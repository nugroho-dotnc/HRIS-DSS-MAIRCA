<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LikertScale extends Model
{
    protected $fillable = [
        'recruitment_criterias_id',
        'label',
        'value',
    ];

    public function criteria(): BelongsTo
    {
        return $this->belongsTo(RecruitmentCriteria::class, 'recruitment_criterias_id', 'id');
    }
}

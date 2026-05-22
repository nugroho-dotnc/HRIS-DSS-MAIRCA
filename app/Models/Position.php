<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Position extends Model
{
    protected $fillable = ['department_id', 'position_name', 'is_active'];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id', 'id');
    }

    public function recruitment_criteria(): HasMany
    {
        return $this->hasMany(RecruitmentCriteria::class, 'position_id', 'id');
    }

    public function vacancies(): HasMany
    {
        return $this->hasMany(Vacancies::class, 'position_id', 'id');
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'position_id', 'id');
    }
}

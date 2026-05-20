<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Position extends Model
{
    protected $fillable = ['department_id', 'position_name', 'is_active'];
    public function department(): BelongsTo{
        return $this->belongsTo(Department::class, 'department_id', 'id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecruitmentResult extends Model
{
    protected $fillable = ['application_id', 'final_score', 'ranking', 'decission'];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InterviewScore extends Model
{
    protected $fillable = ['session_id', 'criteria_id', 'score'];
}

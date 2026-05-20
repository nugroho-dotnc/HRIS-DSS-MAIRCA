<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InterviewSession extends Model
{
    //
    protected $fillable = ['application_id', 'interviewer_id', 'interview_date', 'notes'];
}

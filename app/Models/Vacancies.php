<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vacancies extends Model
{
    protected $fillable = ['hr_id', 'position_id', 'title', 'description', 'requirements', 'deadline', 'status'];
}

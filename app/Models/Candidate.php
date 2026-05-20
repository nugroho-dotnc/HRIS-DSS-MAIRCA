<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Candidate extends Model
{
    protected $fillable = ['name', 'email', 'phone', 'gender', 'city', 'zip_code', 'cv_path', 'portofolio_path', 'experience'];

}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Candidate extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'gender',
        'city',
        'zip_code',
        'cv_path',
        'portofolio_path',
        'web_portofolio_url',
        'complete_address',
        'experience',
    ];

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class, 'candidate_id', 'id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LikertScale extends Model
{
    protected $fillable = ['recruitment_criterias_id', 'label', 'value'];
}

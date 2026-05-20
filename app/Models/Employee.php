<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = ['user_id', 'department_id', 'position_id', 'supervisor_id', 'join_date', 'contract_status'];
}

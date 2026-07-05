<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $fillable=[
        'id',
        'dist_id',
        'taluka_id',
        'bank_id',
        'branch_name',
        'branch_name_ll',
        'status',
    ];
}

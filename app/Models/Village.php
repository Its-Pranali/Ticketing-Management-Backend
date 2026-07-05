<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Village extends Model
{
    protected $fillable=[
        'id',
        'state_id',
        'dist_id',
        'taluka_id',
        'village_name',
        'village_name_ll',
        'status',
    ];
}

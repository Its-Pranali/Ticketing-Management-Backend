<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Taluka extends Model
{
    protected $fillable=[
        'id',
        'state_id',
        'dist_id',
        'taluka_name',
        'taluka_name_ll',
        'status',
    ];
}

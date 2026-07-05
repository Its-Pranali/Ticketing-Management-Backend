<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    protected $fillable=[
        'id',
        'dist_id',
        'bank_name',
        'bank_name_ll',
        'status',
    ];
}

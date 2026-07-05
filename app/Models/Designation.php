<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Designation extends Model
{
    protected $fillable=[
        'id',
        'designation',
        'designation_ll',
        'status',
    ];
}

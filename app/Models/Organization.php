<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    protected $fillable = [
        'id',
        'org_name',
        'org_name_ll',
        'status',
    ];
}

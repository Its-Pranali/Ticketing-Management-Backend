<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    protected $fillable = [
        'id',
        'product_id',
        'module_name',
        'module_name_ll',
        'module_column_name',
        'status',
    ];
}

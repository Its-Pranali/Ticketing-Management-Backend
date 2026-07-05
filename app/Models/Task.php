<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
        'id',
        'product_id',
        'module_id',
        'task_name',
        'task_name_ll',
        'task_column_name',
        'status',
    ];
}

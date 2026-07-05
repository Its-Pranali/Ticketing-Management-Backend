<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pacs extends Model
{
    protected $fillable = [
        'id',
        'state_id',
        'dist_id',
        'taluka_id',
        'bank_id',
        'branch_id',
        'village_id',
        'nabard_pacs_id',
        'pacs_id',
        'pacs_name',
        'pacs_name_ll',
        'ceo_name',
        'ceo_mobile',
        'ceo_email',
        'status',
    ];
}

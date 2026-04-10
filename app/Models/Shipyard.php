<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shipyard extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'owner_name',
        'owner_id_card',
        'phone',
        'address',
        'province_id',
        'ward_id',
        'status',
        'license_number',
        'files',
        'notes',
    ];

    protected $casts = [
        'files' => 'array',
    ];
}

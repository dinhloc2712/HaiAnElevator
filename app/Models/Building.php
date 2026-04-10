<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Building extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'customer_name',
        'address',
        'contact_name',
        'contact_phone',
        'elevator_count',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'elevator_count' => 'integer',
    ];
}

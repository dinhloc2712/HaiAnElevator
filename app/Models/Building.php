<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Building extends Model
{
    use HasFactory, SoftDeletes;

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

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'building_id',
        'elevator_id',
        'total_amount',
        'status',
        'notes',
    ];

    public function building()
    {
        return $this->belongsTo(Building::class);
    }

    public function elevator()
    {
        return $this->belongsTo(Elevator::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}

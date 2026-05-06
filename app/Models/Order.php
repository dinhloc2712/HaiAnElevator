<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Order extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected $fillable = [
        'code',
        'building_id',
        'elevator_id',
        'total_amount',
        'paid_amount',
        'payment_history',
        'status',
        'notes',
    ];

    protected $casts = [
        'payment_history' => 'array',
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

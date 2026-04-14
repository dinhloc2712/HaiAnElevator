<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Elevator extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'building_id',
        'branch_id',
        'customer_name',
        'customer_phone',
        'province',
        'district',
        'manufacturer',
        'model',
        'type',
        'capacity',
        'cycle_days',
        'status',
        'maintenance_deadline',
    ];

    protected $casts = [
        'maintenance_deadline' => 'date',
    ];

    public function building()
    {
        return $this->belongsTo(Building::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function maintenanceChecks()
    {
        return $this->hasMany(MaintenanceCheck::class);
    }
}

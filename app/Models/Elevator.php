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
        'address',
        'manufacturer',
        'model',
        'type',
        'capacity',
        'floors',
        'cycle_days',
        'status',
        'note',
        'map',
        'maintenance_deadline',
        'maintenance_end_date',
    ];

    protected $casts = [
        'maintenance_deadline' => 'date',
        'maintenance_end_date' => 'date',
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

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Incident extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected $fillable = [
        'code',
        'elevator_id',
        'reporter_name',
        'reporter_phone',
        'description',
        'priority',
        'status',
        'reported_at',
        'staff_ids',
        'staff_names',
    ];

    protected $casts = [
        'reported_at' => 'datetime',
        'staff_ids' => 'array',
    ];

    /**
     * Get the elevator associated with the incident.
     */
    public function elevator()
    {
        return $this->belongsTo(Elevator::class);
    }
}

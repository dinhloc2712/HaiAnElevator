<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Incident extends Model
{
    use HasFactory, SoftDeletes;

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

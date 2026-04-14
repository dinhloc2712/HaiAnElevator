<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Incident extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'elevator_id',
        'reporter_name',
        'reporter_phone',
        'description',
        'priority',
        'status',
        'reported_at',
    ];

    protected $casts = [
        'reported_at' => 'datetime',
    ];

    /**
     * Get the elevator associated with the incident.
     */
    public function elevator()
    {
        return $this->belongsTo(Elevator::class);
    }
}

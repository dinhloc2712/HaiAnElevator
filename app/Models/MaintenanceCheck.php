<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceCheck extends Model
{
    use HasFactory;

    protected $fillable = [
        'elevator_id',
        'user_id',
        'status',
        'task_type',
        'scheduled_date',
        'check_date',
        'results',
        'evaluation',
        'staff_ids',
        'staff_names',
        'performer_count',
        'start_time',
        'end_time',
        'notes',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'check_date' => 'date',
        'results' => 'array', 
        'staff_ids' => 'array',
    ];

    /**
     * Get the elevator being maintained.
     */
    public function elevator()
    {
        return $this->belongsTo(Elevator::class);
    }

    /**
     * Get the staff member who created the check.
     */
    public function staff()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

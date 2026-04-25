<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaintenanceCheck extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'elevator_id',
        'user_id',
        'status',
        'task_type',
        'fault_category',
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
        'check_date' => 'date',
        'results' => 'array', 
        'staff_ids' => 'array',
        'fault_category' => 'array',
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

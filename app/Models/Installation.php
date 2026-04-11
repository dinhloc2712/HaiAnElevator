<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Installation extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'branch_id',
        'building_id',
        'user_id',
        'start_date',
        'due_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'due_date' => 'date',
    ];

    /**
     * Get the branch associated with the installation order.
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the building associated with the installation order.
     */
    public function building()
    {
        return $this->belongsTo(Building::class);
    }

    /**
     * Get the staff assigned to this installation order.
     */
    public function staff()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

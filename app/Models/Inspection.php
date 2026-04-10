<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inspection extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'ship_id',
        'inspection_process_id',
        'inspector_id',
        'inspection_date',
        'status',
        'result',
        'notes',
        'fee_amount',
    ];

    protected $casts = [
        'inspection_date' => 'date',
        'fee_amount' => 'decimal:2',
    ];

    public function ship()
    {
        return $this->belongsTo(Ship::class);
    }

    public function process()
    {
        return $this->belongsTo(InspectionProcess::class, 'inspection_process_id');
    }

    public function inspector()
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }

    public function details()
    {
        return $this->hasMany(InspectionDetail::class);
    }
}

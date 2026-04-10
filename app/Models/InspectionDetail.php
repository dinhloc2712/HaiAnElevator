<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InspectionDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'inspection_id',
        'inspection_step_item_id',
        'status',
        'note',
        'evidence_files', // JSON
        'proposal_id',
    ];

    protected $casts = [
        'evidence_files' => 'array',
    ];

    public function inspection()
    {
        return $this->belongsTo(Inspection::class);
    }

    public function item()
    {
        return $this->belongsTo(InspectionStepItem::class, 'inspection_step_item_id');
    }

    public function proposal()
    {
        return $this->belongsTo(Proposal::class);
    }
}

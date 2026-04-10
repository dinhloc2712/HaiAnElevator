<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InspectionStepItem extends Model
{
    use HasFactory;

    protected $fillable = ['inspection_step_id', 'content', 'is_required', 'requires_approval', 'require_all_approvers', 'approvers', 'field_type', 'order_index', 'formula'];

    protected $casts = [
        'approvers' => 'array',
        'require_all_approvers' => 'boolean',
    ];

    public function step()
    {
        return $this->belongsTo(InspectionStep::class, 'inspection_step_id');
    }
}

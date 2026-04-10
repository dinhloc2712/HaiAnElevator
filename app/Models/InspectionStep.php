<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InspectionStep extends Model
{
    use HasFactory;

    protected $fillable = ['inspection_process_id', 'title', 'order_index'];

    public function process()
    {
        return $this->belongsTo(InspectionProcess::class, 'inspection_process_id');
    }

    public function items()
    {
        return $this->hasMany(InspectionStepItem::class)->orderBy('order_index');
    }
}

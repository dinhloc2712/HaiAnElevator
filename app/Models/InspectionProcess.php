<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InspectionProcess extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'is_active'];

    public function steps()
    {
        return $this->hasMany(InspectionStep::class)->orderBy('order_index');
    }
}

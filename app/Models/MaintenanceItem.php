<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceItem extends Model
{
    use HasFactory;

    protected $fillable = ['category_id', 'name', 'sort_order'];

    public function category()
    {
        return $this->belongsTo(MaintenanceCategory::class, 'category_id');
    }
}

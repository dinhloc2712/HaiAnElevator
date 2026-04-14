<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'sort_order'];

    public function items()
    {
        return $this->hasMany(MaintenanceItem::class, 'category_id')->orderBy('sort_order');
    }
}

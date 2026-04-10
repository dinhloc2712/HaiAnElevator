<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KpiResetLog extends Model
{
    public $timestamps = false;

    protected $fillable = ['reset_by', 'snapshot', 'reset_at', 'note'];

    protected $casts = [
        'snapshot' => 'array',
        'reset_at' => 'datetime',
    ];

    public function resetByUser()
    {
        return $this->belongsTo(User::class, 'reset_by');
    }
}

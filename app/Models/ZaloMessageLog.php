<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ZaloMessageLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'phone',
        'channel',
        'template_id',
        'tracking_id',
        'msg_id',
        'status',
        'error_code',
        'error_message',
        'response',
        'payload',
    ];

    protected $casts = [
        'response' => 'array',
        'payload' => 'array',
    ];
}

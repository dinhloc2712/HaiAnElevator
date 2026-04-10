<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewsRead extends Model
{
    use HasFactory;

    protected $fillable = [
        'news_id',
        'user_id',
        'read_at'
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function news()
    {
        return $this->belongsTo(News::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

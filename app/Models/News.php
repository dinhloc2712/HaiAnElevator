<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'title',
        'content',
        'attachment',
        'recipient_type',
        'recipient_ids',
        'created_by'
    ];

    protected $casts = [
        'recipient_ids' => 'array',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reads()
    {
        return $this->hasMany(NewsRead::class);
    }

    public function isReadBy(User $user)
    {
        return $this->reads()->where('user_id', $user->id)->exists();
    }
}

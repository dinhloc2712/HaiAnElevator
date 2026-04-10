<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Ship;

class Proposal extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'category',
        'content',
        'pre_vat_amount',
        'vat',
        'amount',
        'paid_amount',
        'user_id',
        'approver_id',
        'ship_id',
        'status',
        'rejection_reason',
        'expiration_date',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function ship()
    {
        return $this->belongsTo(Ship::class);
    }

    public function steps()
    {
        return $this->hasMany(ProposalStep::class)->orderBy('step_level');
    }
}

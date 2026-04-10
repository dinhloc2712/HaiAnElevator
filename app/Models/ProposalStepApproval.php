<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProposalStepApproval extends Model
{
    protected $fillable = ['proposal_step_id', 'user_id', 'status', 'comment', 'acted_at'];

    protected $casts = [
        'acted_at' => 'datetime',
    ];

    public function step()
    {
        return $this->belongsTo(ProposalStep::class, 'proposal_step_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

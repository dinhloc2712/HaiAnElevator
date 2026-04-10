<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProposalStep extends Model
{
    protected $fillable = [
        'proposal_id',
        'step_level',
        'name',
        'approval_type',
        'status',
        'attachment_files',
        'amount',
    ];

    protected $casts = [
        'attachment_files' => 'array',
    ];

    protected $appends = ['attachment_urls'];

    public function getAttachmentUrlsAttribute()
    {
        $urls = [];
        if (!empty($this->attachment_files) && is_array($this->attachment_files)) {
            foreach ($this->attachment_files as $filename) {
                $urls[] = [
                    'filename' => $filename,
                    'url'      => route('admin.media.serve', ['filename' => $filename]),
                ];
            }
        }
        return $urls;
    }

    public function proposal()
    {
        return $this->belongsTo(Proposal::class);
    }

    public function approvals()
    {
        return $this->hasMany(ProposalStepApproval::class);
    }
}

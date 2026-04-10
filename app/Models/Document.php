<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = [
        'uploaded_by',
        'document_type',
        'title',
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
        'notes',
    ];

    public static array $types = [
        'cccd'      => 'CCCD / Chứng minh nhân dân',
        'passport'  => 'Hộ chiếu',
        'contract'  => 'Hợp đồng lao động',
        'degree'    => 'Bằng cấp / Chứng chỉ',
        'insurance' => 'Bảo hiểm',
        'other'     => 'Khác',
    ];

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getTypeLabelAttribute(): string
    {
        return static::$types[$this->document_type] ?? 'Khác';
    }

    public function getFileSizeFormattedAttribute(): string
    {
        $size = $this->file_size;
        if ($size >= 1048576) return round($size / 1048576, 2) . ' MB';
        if ($size >= 1024)    return round($size / 1024, 2) . ' KB';
        return $size . ' B';
    }
}

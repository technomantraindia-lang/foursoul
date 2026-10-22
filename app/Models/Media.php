<?php

namespace App\Models;

use App\Enums\MediaType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Media extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'media';

    protected $fillable = [
        'uuid',
        'folder_id',
        'uploaded_by_admin_id',
        'name',
        'file_name',
        'path',
        'disk',
        'mime_type',
        'type',
        'size_bytes',
        'width',
        'height',
        'alt_text',
        'title',
        'caption',
        'variants',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'type' => MediaType::class,
            'variants' => 'array',
            'metadata' => 'array',
            'size_bytes' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
        ];
    }

    public function folder()
    {
        return $this->belongsTo(MediaFolder::class, 'folder_id');
    }

    public function uploadedBy()
    {
        return $this->belongsTo(Admin::class, 'uploaded_by_admin_id');
    }
}
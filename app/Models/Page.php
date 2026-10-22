<?php

namespace App\Models;

use App\Enums\PageStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Page extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pages';

    protected $fillable = [
        'uuid',
        'title',
        'slug',
        'seo_title',
        'seo_description',
        'og_image_path',
        'canonical_url',
        'layout',
        'visibility',
        'status',
        'published_at',
        'content',
        'version',
        'metadata',
        'created_by_admin_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => PageStatus::class,
            'published_at' => 'datetime',
            'content' => 'array',
            'metadata' => 'array',
            'version' => 'integer',
        ];
    }

    public function createdBy()
    {
        return $this->belongsTo(Admin::class, 'created_by_admin_id');
    }

    public function scopePublished($query)
    {
        return $query
            ->where('status', PageStatus::Published)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->where('visibility', 'public');
    }

    /**
     * JSON content tree stored on the page row (no separate sections table).
     *
     * @return array<int, array<string, mixed>>
     */
    public function contentSections(): array
    {
        $content = $this->content ?? [];

        if (isset($content['sections']) && is_array($content['sections'])) {
            return $content['sections'];
        }

        return is_array($content) ? $content : [];
    }
}
<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\LessonType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Phase 2 — leaf content unit inside a course section.
 *
 * `type` discriminates between video / text / quiz lessons; media and
 * duration are optional so text lessons stay lightweight.
 */
class CourseLesson extends Model
{
    /** @use HasFactory<\Database\Factories\CourseLessonFactory> */
    use HasFactory;

    protected $fillable = [
        'uuid',
        'course_section_id',
        'title',
        'type',
        'content',
        'video_url',
        'duration_minutes',
        'position',
        'is_free_preview',
        'release_type',
        'release_at',
        'requires_previous_completion',
        'completion_requirement',
        'completion_threshold',
        'required_watch_seconds',
        'duration_seconds',
        'is_published',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => LessonType::class,
            'duration_minutes' => 'integer',
            'position' => 'integer',
            'is_free_preview' => 'boolean',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function section(): BelongsTo
    {
        return $this->belongsTo(CourseSection::class, 'course_section_id');
    }

    public function courseSection(): BelongsTo
    {
        return $this->section();
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    /**
     * Scope to lessons usable as free previews (published courses only).
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeFreePreview(Builder $query): Builder
    {
        return $query->where('is_free_preview', true);
    }

    // -------------------------------------------------------------------------
    // Lifecycle hooks
    // -------------------------------------------------------------------------

    protected static function booted(): void
    {
        static::creating(function (self $lesson): void {
            $lesson->uuid ??= (string) Str::uuid();

            // Default to the next slot at the end of the parent section.
            if ($lesson->position === null) {
                $lesson->position = ((int) static::query()
                    ->where('course_section_id', $lesson->course_section_id)
                    ->max('position')) + 1;
            }
        });
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * Phase 2 — ordered grouping of lessons inside a course.
 *
 * Position is an integer sort order managed by the Filament relation
 * manager; reordering never touches other courses.
 */
class CourseSection extends Model
{
    /** @use HasFactory<\Database\Factories\CourseSectionFactory> */
    use HasFactory;

    protected $fillable = [
        'uuid',
        'course_id',
        'title',
        'description',
        'position',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'position' => 'integer',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * @return HasMany<CourseLesson, $this>
     */
    public function lessons(): HasMany
    {
        return $this->hasMany(CourseLesson::class)->orderBy('position');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    /**
     * Scope to sections belonging to a given course, ordered by position.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeForCourse(Builder $query, int $courseId): Builder
    {
        return $query->where('course_id', $courseId)->orderBy('position');
    }

    // -------------------------------------------------------------------------
    // Lifecycle hooks
    // -------------------------------------------------------------------------

    protected static function booted(): void
    {
        static::creating(function (self $section): void {
            $section->uuid ??= (string) Str::uuid();

            // Default to the next slot at the end of the parent course.
            if ($section->position === null) {
                $section->position = ((int) static::query()
                    ->where('course_id', $section->course_id)
                    ->max('position')) + 1;
            }
        });
    }
}

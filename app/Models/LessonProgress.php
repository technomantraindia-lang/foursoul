<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\LessonProgressStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class LessonProgress extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'user_id',
        'course_id',
        'lesson_id',
        'enrollment_id',
        'status',
        'started_at',
        'last_accessed_at',
        'completed_at',
        'watch_seconds',
        'duration_seconds',
        'progress_percentage',
        'last_position_seconds',
        'completion_source',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'status' => LessonProgressStatus::class,
            'metadata' => 'array',
            'progress_percentage' => 'decimal:2',
            'started_at' => 'datetime',
            'last_accessed_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $progress) {
            if (! $progress->uuid) {
                $progress->uuid = (string) Str::uuid();
            }
        });
    }

    /* -----------------------------------------------------------------
     | Relationships
     * ----------------------------------------------------------------- */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(CourseLesson::class, 'lesson_id');
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    /* -----------------------------------------------------------------
     | State helpers
     * ----------------------------------------------------------------- */

    public function isCompleted(): bool
    {
        return $this->status === LessonProgressStatus::Completed;
    }
}
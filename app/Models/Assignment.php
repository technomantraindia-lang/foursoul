<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AssignmentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Assignment extends Model
{
    protected static function booted(): void
    {
        static::creating(function (self $assignment): void {
            $assignment->uuid ??= (string) Str::uuid();
        });
    }

    protected $fillable = [
        'uuid',
        'course_id',
        'course_lesson_id',
        'title',
        'description',
        'instructions',
        'due_at',
        'max_marks',
        'allow_resubmission',
        'max_attempts',
        'accepted_file_types',
        'max_file_size_kb',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'due_at' => 'datetime',
            'max_marks' => 'decimal:2',
            'allow_resubmission' => 'boolean',
            'max_attempts' => 'integer',
            'accepted_file_types' => 'array',
            'max_file_size_kb' => 'integer',
            'status' => AssignmentStatus::class,
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(CourseLesson::class, 'course_lesson_id');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(AssignmentSubmission::class);
    }

    public function isPublished(): bool
    {
        return $this->status === AssignmentStatus::Published;
    }
}

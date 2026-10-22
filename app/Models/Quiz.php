<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\QuizStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

use Illuminate\Support\Str;

class Quiz extends Model
{
    protected $fillable = [
        'uuid',
        'course_id',
        'course_lesson_id',
        'title',
        'description',
        'passing_marks',
        'total_marks',
        'max_attempts',
        'duration_minutes',
        'shuffle_questions',
        'shuffle_options',
        'random_question_count',
        'status',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'passing_marks' => 'decimal:2',
            'total_marks' => 'decimal:2',
            'max_attempts' => 'integer',
            'duration_minutes' => 'integer',
            'shuffle_questions' => 'boolean',
            'shuffle_options' => 'boolean',
            'random_question_count' => 'integer',
            'status' => QuizStatus::class,
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $quiz): void {
            if (blank($quiz->uuid)) {
                $quiz->uuid = (string) \Illuminate\Support\Str::uuid();
            }
        });
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

    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'quiz_questions')
            ->withPivot(['sort_order', 'marks_override'])
            ->orderByPivot('sort_order');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }

    public function isPublished(): bool
    {
        return $this->status === QuizStatus::Published
            && ($this->published_at === null || $this->published_at->lte(now()));
    }
}
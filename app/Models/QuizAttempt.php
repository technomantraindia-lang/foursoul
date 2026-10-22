<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AttemptStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuizAttempt extends Model
{
    protected $fillable = [
        'uuid',
        'quiz_id',
        'user_id',
        'enrollment_id',
        'attempt_number',
        'status',
        'question_order',
        'started_at',
        'submitted_at',
        'completed_at',
        'expires_at',
        'score',
        'percentage',
        'passed',
        'time_taken_seconds',
    ];

    protected function casts(): array
    {
        return [
            'attempt_number' => 'integer',
            'status' => AttemptStatus::class,
            'question_order' => 'array',
            'started_at' => 'datetime',
            'submitted_at' => 'datetime',
            'completed_at' => 'datetime',
            'expires_at' => 'datetime',
            'score' => 'decimal:2',
            'percentage' => 'decimal:2',
            'passed' => 'boolean',
            'time_taken_seconds' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(QuizAnswer::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && now()->gt($this->expires_at);
    }

    public function isLocked(): bool
    {
        return in_array($this->status, [AttemptStatus::Submitted, AttemptStatus::Completed, AttemptStatus::Expired], true);
    }
}
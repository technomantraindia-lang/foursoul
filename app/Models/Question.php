<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\QuestionDifficulty;
use App\Enums\QuestionStatus;
use App\Enums\QuestionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Question extends Model
{
    protected $fillable = [
        'uuid',
        'question_category_id',
        'type',
        'question_text',
        'explanation',
        'marks',
        'negative_marks',
        'difficulty',
        'status',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'marks' => 'decimal:2',
            'negative_marks' => 'decimal:2',
            'metadata' => 'array',
            'type' => QuestionType::class,
            'difficulty' => QuestionDifficulty::class,
            'status' => QuestionStatus::class,
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $question): void {
            if (blank($question->uuid)) {
                $question->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(QuestionCategory::class, 'question_category_id');
    }

    public function options(): HasMany
    {
        return $this->hasMany(QuestionOption::class)->orderBy('sort_order');
    }

    public function isAutoGradable(): bool
    {
        return in_array($this->type, [
            QuestionType::SingleChoice,
            QuestionType::MultipleChoice,
            QuestionType::TrueFalse,
            QuestionType::Numeric,
        ], true);
    }
}
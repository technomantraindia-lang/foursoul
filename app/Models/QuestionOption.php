<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Illuminate\Support\Str;

class QuestionOption extends Model
{
    protected $fillable = ['uuid', 'question_id', 'option_text', 'is_correct', 'sort_order'];

    protected function casts(): array
    {
        return [
            'is_correct' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $option): void {
            if (blank($option->uuid)) {
                $option->uuid = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}
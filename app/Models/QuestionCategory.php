<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestionCategory extends Model
{
    protected $fillable = ['uuid', 'name', 'slug', 'description'];

    protected function casts(): array
    {
        return [];
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }
}
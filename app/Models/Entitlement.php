<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EntitlementStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Entitlement extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'user_id',
        'subscription_id',
        'course_id',
        'status',
        'granted_at',
        'expires_at',
        'revoked_at',
        'revoked_reason',
        'source',
        'source_reference',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'status' => EntitlementStatus::class,
            'metadata' => 'array',
            'granted_at' => 'datetime',
            'expires_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $entitlement) {
            if (! $entitlement->uuid) {
                $entitlement->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /* -----------------------------------------------------------------
     | Relationships
     * ----------------------------------------------------------------- */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(UserSubscription::class, 'subscription_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /* -----------------------------------------------------------------
     | Scopes
     * ----------------------------------------------------------------- */

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', EntitlementStatus::Active);
    }

    public function scopeForUser(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->id);
    }

    public function scopeForCourse(Builder $query, Course $course): Builder
    {
        return $query->where('course_id', $course->id);
    }

    /* -----------------------------------------------------------------
     | State helpers
     * ----------------------------------------------------------------- */

    public function grantsAccess(): bool
    {
        if ($this->status !== EntitlementStatus::Active) {
            return false;
        }

        if ($this->expires_at && now()->gt($this->expires_at)) {
            return false;
        }

        return true;
    }
}
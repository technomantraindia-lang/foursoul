<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EnrollmentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Enrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'user_id',
        'course_id',
        'entitlement_id',
        'status',
        'source',
        'enrolled_at',
        'expires_at',
        'completed_at',
        'revoked_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'status' => EnrollmentStatus::class,
            'metadata' => 'array',
            'enrolled_at' => 'datetime',
            'expires_at' => 'datetime',
            'completed_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $enrollment) {
            if (! $enrollment->uuid) {
                $enrollment->uuid = (string) Str::uuid();
            }
            if (blank($enrollment->enrolled_at)) {
                $enrollment->enrolled_at = now();
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

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function entitlement(): BelongsTo
    {
        return $this->belongsTo(Entitlement::class, 'entitlement_id');
    }

    /* -----------------------------------------------------------------
     | Scopes
     * ----------------------------------------------------------------- */

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', EnrollmentStatus::Active);
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

    /**
     * Lock access without deleting history or progress.
     */
    public function lock(?string $reason = null): void
    {
        if ($this->status === EnrollmentStatus::Active) {
            $this->forceFill([
                'status' => EnrollmentStatus::Locked,
            ])->save();
        }
    }

    /**
     * Restore access on renewal without resetting progress.
     */
    public function unlock(): void
    {
        if ($this->status === EnrollmentStatus::Locked) {
            $this->forceFill([
                'status' => EnrollmentStatus::Active,
            ])->save();
        }
    }

    public function grantsAccess(): bool
    {
        return $this->status->grantsAccess();
    }
}
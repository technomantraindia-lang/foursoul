<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\LiveSessionStatus;
use App\Enums\SessionProvider;
use App\Enums\SessionType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class LiveSession extends Model
{
    /** @use HasFactory<\Database\Factories\LiveSessionFactory> */
    use HasFactory;

    protected $fillable = [
        'uuid',
        'course_id',
        'lesson_id',
        'instructor_id',
        'title',
        'description',
        'session_type',
        'video_type',
        'provider',
        'meeting_id',
        'meeting_password',
        'meeting_url',
        'video_url',
        'video_file',
        'host_url',
        'replay_url',
        'recording_url',
        'allowed_plans',
        'min_plan_tier',
        'is_locked_for_non_members',
        'timezone',
        'scheduled_start_at',
        'scheduled_end_at',
        'actual_start_at',
        'actual_end_at',
        'max_participants',
        'waiting_room_enabled',
        'recording_enabled',
        'replay_enabled',
        'status',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'session_type' => SessionType::class,
            'provider' => SessionProvider::class,
            'status' => LiveSessionStatus::class,
            'allowed_plans' => 'array',
            'is_locked_for_non_members' => 'boolean',
            'metadata' => 'array',
            'max_participants' => 'integer',
            'waiting_room_enabled' => 'boolean',
            'recording_enabled' => 'boolean',
            'replay_enabled' => 'boolean',
            'scheduled_start_at' => 'datetime',
            'scheduled_end_at' => 'datetime',
            'actual_start_at' => 'datetime',
            'actual_end_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $session): void {
            if (! $session->uuid) {
                $session->uuid = (string) Str::uuid();
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

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(CourseLesson::class, 'lesson_id');
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'instructor_id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(SessionAttendance::class, 'live_session_id');
    }

    /* -----------------------------------------------------------------
     | Scopes
     * ----------------------------------------------------------------- */

    /**
     * Sessions visible to learners: scheduled/live/completed only.
     *
     * @param Builder<self> $query
     */
    public function scopeVisibleToLearners(Builder $query): Builder
    {
        return $query->whereIn('status', [
            LiveSessionStatus::Scheduled->value,
            LiveSessionStatus::Live->value,
            LiveSessionStatus::Completed->value,
        ]);
    }

    /**
     * @param Builder<self> $query
     */
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('scheduled_start_at', '>=', now())
            ->whereIn('status', [LiveSessionStatus::Scheduled->value]);
    }

    /**
     * @param Builder<self> $query
     */
    public function scopePast(Builder $query): Builder
    {
        return $query->where('scheduled_end_at', '<', now());
    }

    /* -----------------------------------------------------------------
     | State helpers
     * ----------------------------------------------------------------- */

    public function isJoinableNow(int $beforeMinutes = 15, int $graceMinutes = 30): bool
    {
        if ($this->status !== LiveSessionStatus::Scheduled && $this->status !== LiveSessionStatus::Live) {
            return false;
        }

        $now = now($this->timezone);
        $start = $this->scheduled_start_at->copy()->setTimezone($this->timezone);
        $end = $this->scheduled_end_at->copy()->setTimezone($this->timezone);

        return $now->gte($start->copy()->subMinutes($beforeMinutes))
            && $now->lte($end->copy()->addMinutes($graceMinutes));
    }

    /**
     * Meeting links are temporary session credentials. Remove expired links
     * while retaining the session and any uploaded or recorded video.
     */
    public static function clearExpiredMeetingLinks(): int
    {
        return static::query()
            ->whereNotNull('meeting_url')
            ->whereNotNull('scheduled_end_at')
            ->where('scheduled_end_at', '<=', now())
            ->where(static function (Builder $query): void {
                $query
                    ->where('video_type', 'live_meeting')
                    ->orWhereJsonContains('metadata->content_kind', 'meeting');
            })
            ->update([
                'meeting_url' => null,
                'meeting_password' => null,
                'updated_at' => now(),
            ]);
    }

    public function isReplayAvailable(): bool
    {
        return $this->status === LiveSessionStatus::Completed
            && $this->replay_enabled
            && filled($this->recording_url ?? $this->replay_url);
    }
}

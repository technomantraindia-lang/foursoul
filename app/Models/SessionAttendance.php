<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class SessionAttendance extends Model
{
    /** @use HasFactory<\Database\Factories\SessionAttendanceFactory> */
    use HasFactory;

    protected $table = 'session_attendance';

    protected $fillable = [
        'uuid',
        'live_session_id',
        'enrollment_id',
        'user_id',
        'joined_at',
        'left_at',
        'duration_minutes',
        'attendance_percentage',
        'present',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'joined_at' => 'datetime',
            'left_at' => 'datetime',
            'duration_minutes' => 'integer',
            'attendance_percentage' => 'decimal:2',
            'present' => 'boolean',
            'metadata' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $attendance): void {
            if (! $attendance->uuid) {
                $attendance->uuid = (string) Str::uuid();
            }
        });
    }

    /* -----------------------------------------------------------------
     | Relationships
     * ----------------------------------------------------------------- */

    public function liveSession(): BelongsTo
    {
        return $this->belongsTo(LiveSession::class, 'live_session_id');
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
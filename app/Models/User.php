<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UserStatus;
use App\Support\Currency;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $table = 'users';

    /**
     * Mass-assignment protection (Prompt 2A §30).
     *
     * Only registration/profile/preference inputs are fillable.
     * Deliberately NOT fillable (admin/system-controlled):
     * status, email_verified_at, mobile_verified_at,
     * detected_country_code, detected_currency, last_detected_ip,
     * last_login_at, last_login_ip, last_activity_at,
     * referral_code, referred_by_user_id, terms/privacy timestamps.
     */
    protected $fillable = [
        'full_name',
        'first_name',
        'last_name',
        'email',
        'mobile_country_code',
        'mobile_number',
        'password',
        'avatar_path',
        'preferred_language',
        'preferred_currency',
        'timezone',
        'marketing_consent',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'mobile_verified_at' => 'datetime',
            'marketing_consent' => 'boolean',
            'notification_preferences' => 'array',
            'marketing_consent_at' => 'datetime',
            'terms_accepted_at' => 'datetime',
            'privacy_accepted_at' => 'datetime',
            'status' => UserStatus::class,
            'preferred_currency' => Currency::class,
            'last_login_at' => 'datetime',
            'last_activity_at' => 'datetime',
            'password' => 'hashed',
            'deleted_at' => 'datetime',
        ];
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Channel-level notification preferences with safe defaults.
     */
    public function notificationPreferences(): array
    {
        return array_merge([
            'email' => true,
            'whatsapp' => true,
            'push' => true,
            'sms' => false,
            'in_app' => true,
            'marketing' => (bool) $this->marketing_consent,
            'reminders' => [
                'subscription_expiry' => true,
                'upcoming_live_session' => true,
                'assignment_due' => true,
                'quiz_due' => true,
                'certificate_issued' => true,
                'payment_success' => true,
                'invoice_ready' => true,
            ],
        ], $this->notification_preferences ?? []);
    }

    protected static function booted(): void
    {
        static::creating(function (self $user): void {
            // Prompt 2A §3: UUID is system-generated only; never accepted
            // from a request because it is not fillable.
            if (! $user->uuid) {
                $user->uuid = (string) Str::uuid();
            }

            if (! $user->status instanceof UserStatus) {
                $user->status = UserStatus::Active;
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function referredBy(): BelongsTo
    {
        return $this->belongsTo(self::class, 'referred_by_user_id');
    }

    public function referrals()
    {
        return $this->hasMany(self::class, 'referred_by_user_id');
    }

    public function subscriptions()
    {
        return $this->hasMany(UserSubscription::class);
    }

    public function activeSubscription()
    {
        return $this->hasOne(UserSubscription::class)->where('status', \App\Enums\SubscriptionStatus::Active)->latestOfMany();
    }

    public function entitlements()
    {
        return $this->hasMany(Entitlement::class);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }

    public function lessonProgress()
    {
        return $this->hasMany(LessonProgress::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Verification / status helpers
    |--------------------------------------------------------------------------
    */

    public function getNameAttribute(): string
    {
        return (string) ($this->attributes['full_name'] ?? trim(($this->first_name ?? '').' '.($this->last_name ?? '')));
    }

    public function hasVerifiedEmail(): bool
    {
        return $this->email_verified_at !== null;
    }

    public function hasVerifiedMobile(): bool
    {
        return $this->mobile_verified_at !== null;
    }

    public function isActive(): bool
    {
        return $this->status === UserStatus::Active;
    }

    public function isSuspended(): bool
    {
        return $this->status === UserStatus::Suspended;
    }

    public function isBlocked(): bool
    {
        return $this->status === UserStatus::Blocked;
    }

    public function isClosed(): bool
    {
        return $this->status === UserStatus::Closed;
    }

    /**
     * Split full_name into first/last on write so both representations
     * stay consistent. Called by services after setting full_name.
     */
    public function syncNameParts(): void
    {
        $parts = preg_split('/\s+/', trim((string) $this->full_name)) ?: [];

        $this->first_name = $parts[0] ?? null;
        $this->last_name = count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : null;
    }

    /**
     * Record consent timestamps + versions atomically with the model save
     * performed by the caller. Versions come from config so they can be
     * bumped when legal documents change.
     */
    public function applyConsents(bool $marketingConsent): void
    {
        $now = now();

        $this->terms_accepted_at = $now;
        $this->terms_version = (string) config('legal.terms.version', '1.0');

        $this->privacy_accepted_at = $now;
        $this->privacy_version = (string) config('legal.privacy.version', '1.0');

        $this->marketing_consent = $marketingConsent;
        $this->marketing_consent_at = $marketingConsent ? $now : null;
    }

    /**
     * Generate a unique referral code for this user (used at registration).
     */
    public static function generateReferralCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (DB::table('users')->where('referral_code', $code)->exists());

        return $code;
    }
}
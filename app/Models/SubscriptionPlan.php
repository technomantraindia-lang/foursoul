<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PlanBillingType;
use App\Enums\PlanCourseAccessType;
use App\Enums\PlanStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'name',
        'slug',
        'short_description',
        'description',
        'status',
        'billing_type',
        'duration_days',
        'trial_enabled',
        'trial_days',
        'course_access_type',
        'price_aed',
        'sale_price_aed',
        'price_usd',
        'sale_price_usd',
        'price_inr',
        'sale_price_inr',
        'sale_start_at',
        'sale_end_at',
        'is_featured',
        'sort_order',
        'max_active_enrollments',
        'max_devices',
        'live_sessions_enabled',
        'community_access_enabled',
        'metadata',
        'published_at',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => PlanStatus::class,
            'billing_type' => PlanBillingType::class,
            'course_access_type' => PlanCourseAccessType::class,
            'trial_enabled' => 'boolean',
            'is_featured' => 'boolean',
            'live_sessions_enabled' => 'boolean',
            'community_access_enabled' => 'boolean',
            'duration_days' => 'integer',
            'trial_days' => 'integer',
            'sort_order' => 'integer',
            'max_active_enrollments' => 'integer',
            'max_devices' => 'integer',
            'metadata' => 'array',
            'sale_start_at' => 'datetime',
            'sale_end_at' => 'datetime',
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $plan) {
            if (! $plan->uuid) {
                $plan->uuid = (string) Str::uuid();
            }
            if (blank($plan->slug)) {
                $plan->slug = Str::slug((string) $plan->name) ?: 'plan';
            }
        });

        static::saving(function (self $plan): void {
            if ($plan->status === PlanStatus::Active && $plan->published_at === null) {
                $plan->published_at = now();
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

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'subscription_plan_courses')
            ->withTimestamps();
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(UserSubscription::class, 'subscription_plan_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'updated_by');
    }

    /* -----------------------------------------------------------------
     | Scopes
     * ----------------------------------------------------------------- */

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', PlanStatus::Active);
    }

    public function scopePurchasable(Builder $query): Builder
    {
        return $query->where('status', PlanStatus::Active)
            ->whereNotNull('published_at');
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /* -----------------------------------------------------------------
     | Effective pricing (single source of truth — never duplicate in views)
     * ----------------------------------------------------------------- */

    /**
     * Whether the configured sale window is currently effective.
     */
    public function isSaleWindowOpen(?\Illuminate\Support\Carbon $at = null): bool
    {
        $now = $at ?? now();

        if ($this->sale_start_at && $now->lt($this->sale_start_at)) {
            return false;
        }

        if ($this->sale_end_at && $now->gt($this->sale_end_at)) {
            return false;
        }

        return true;
    }

    /**
     * Effective unit price for a currency code (aed|usd|inr).
     */
    public function effectivePrice(string $currency): ?float
    {
        $currency = strtolower($currency);

        $base = match ($currency) {
            'aed' => $this->price_aed,
            'usd' => $this->price_usd,
            'inr' => $this->price_inr,
            default => null,
        };

        if ($base === null) {
            return null;
        }

        $sale = match ($currency) {
            'aed' => $this->sale_price_aed,
            'usd' => $this->sale_price_usd,
            'inr' => $this->sale_price_inr,
            default => null,
        };

        if ($sale !== null && $sale <= $base && $this->isSaleWindowOpen()) {
            return (float) $sale;
        }

        return (float) $base;
    }

    /**
     * Access duration in days; null = lifetime.
     */
    public function accessDurationDays(): ?int
    {
        if ($this->billing_type === PlanBillingType::Lifetime) {
            return null;
        }

        return $this->duration_days ?? match ($this->billing_type) {
            PlanBillingType::Monthly => 30,
            PlanBillingType::Quarterly => 91,
            PlanBillingType::HalfYearly => 182,
            PlanBillingType::Yearly => 365,
            default => null,
        };
    }

    /**
     * Courses this plan grants access to, honouring course purchase modes:
     * standalone_only courses are never granted via a plan.
     */
    public function eligibleCourses()
    {
        $query = Course::query()->published();

        if ($this->course_access_type === PlanCourseAccessType::SelectedCourses) {
            $query->whereIn('courses.id', $this->courses()->pluck('courses.id'));
        }

        // Exclude standalone-only courses from plan-based access.
        $query->whereNot('purchase_mode', 'standalone_only');

        return $query;
    }

    /**
     * Whether this plan grants access to the given course.
     */
    public function grantsCourse(Course $course): bool
    {
        if ($course->purchase_mode === 'standalone_only') {
            return false;
        }

        if ($this->course_access_type === PlanCourseAccessType::AllCourses) {
            return true;
        }

        return $this->courses()->whereKey($course->getKey())->exists();
    }
}

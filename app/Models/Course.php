<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CourseStatus;
use App\Enums\PurchaseMode;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth as AuthFacade;
use Illuminate\Support\Str;

/**
 * Phase 2 — root entity of the learning catalogue.
 *
 * Authored by an admin, lifecycle-managed through CourseStatus and
 * soft-deleted so historical enrollments keep referential integrity.
 */
class Course extends Model
{
    /** @use HasFactory<\Database\Factories\CourseFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'title',
        'slug',
        'description',
        'meeting_platform',
        'meeting_link',
        'meeting_passcode',
        'meeting_schedule',
        'requires_membership',
        'status',
        'purchase_mode',
        'published_at',
        'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => CourseStatus::class,
            'purchase_mode' => PurchaseMode::class,
            'requires_membership' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function author(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    /**
     * @return HasMany<CourseSection, $this>
     */
    public function sections(): HasMany
    {
        return $this->hasMany(CourseSection::class)->orderBy('position');
    }

    /**
     * Nested lessons via sections — required for implicit child route binding
     * on /courses/{course}/preview/{lesson} and /learn/.../lessons/{lesson}.
     */
    public function lessons(): HasManyThrough
    {
        return $this->hasManyThrough(CourseLesson::class, CourseSection::class);
    }

    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function subscriptionPlans()
    {
        return $this->belongsToMany(SubscriptionPlan::class, 'subscription_plan_courses');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    /**
     * Scope to courses visible in the customer-facing catalogue.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', CourseStatus::Published->value);
    }

    // -------------------------------------------------------------------------
    // Commerce pricing
    // -------------------------------------------------------------------------

    /**
     * Effective standalone purchase price for a currency code (aed|usd|inr).
     * Returns null when the course has no price configured for that currency.
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

        return (float) $base;
    }

    // -------------------------------------------------------------------------
    // Lifecycle hooks
    // -------------------------------------------------------------------------

    protected static function booted(): void
    {
        static::creating(function (self $course): void {
            $course->uuid ??= (string) Str::uuid();

            if (blank($course->slug)) {
                $course->slug = $course->generateUniqueSlug((string) $course->title);
            }

            // Attribute authorship to the authenticated admin when not supplied.
            $course->created_by ??= AuthFacade::guard('admin')->id();
        });

        static::deleting(function (self $course): void {
            if ($course->slug && ! str_contains((string) $course->slug, '-retired-')) {
                $course->slug = $course->slug.'-retired-'.$course->getKey();
                $course->saveQuietly();
            }
        });

        // Publishing stamps published_at exactly once; archiving keeps history.
        static::saving(function (self $course): void {
            if ($course->status === CourseStatus::Published && $course->published_at === null) {
                $course->published_at = now();
            }
        });
    }

    /**
     * Slug unique among non-deleted courses (soft-deleted slugs stay retired).
     */
    protected function generateUniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'course';
        $slug = $base;
        $suffix = 1;

        while (static::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.(++$suffix);
        }

        return $slug;
    }
}

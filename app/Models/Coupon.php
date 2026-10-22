<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CouponType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Phase 5 (§11): Discount coupon.
 */
class Coupon extends Model
{
    protected $fillable = [
        'uuid',
        'code',
        'type',
        'value',
        'currency',
        'applicable_course_ids',
        'applicable_plan_ids',
        'usage_limit',
        'usage_limit_per_user',
        'used_count',
        'minimum_order_amount',
        'maximum_discount_amount',
        'starts_at',
        'ends_at',
        'status',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'uuid' => 'string',
            'type' => CouponType::class,
            'value' => 'decimal:2',
            'used_count' => 'integer',
            'usage_limit' => 'integer',
            'usage_limit_per_user' => 'integer',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $coupon) {
            $coupon->uuid ??= (string) \Illuminate\Support\Str::uuid();
        });
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', now()));
    }

    public function hasUsesRemaining(): bool
    {
        return $this->usage_limit === null || $this->used_count < $this->usage_limit;
    }
}
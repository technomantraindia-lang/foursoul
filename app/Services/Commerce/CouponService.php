<?php

declare(strict_types=1);

namespace App\Services\Commerce;

use App\Enums\CouponType;
use App\Models\Coupon;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Phase 5 (§11): Coupon validation + redemption.
 * Server-side only — never trust a frontend-computed discount.
 */
class CouponService
{
    /**
     * Validate a coupon for a user + subtotal (in minor units) + currency.
     *
     * @return array{coupon:Coupon, discount:int}
     */
    public function validate(string $code, User $user, int $subtotal, string $currency): array
    {
        $currency = strtolower($currency);

        /** @var Coupon|null $coupon */
        $coupon = Coupon::query()->active()->where('code', strtoupper(trim($code)))->first();

        if (! $coupon) {
            throw new \InvalidArgumentException('This coupon code is invalid or has expired.');
        }

        if (! $coupon->hasUsesRemaining()) {
            throw new \InvalidArgumentException('This coupon has reached its usage limit.');
        }

        if ($coupon->usage_limit_per_user > 0) {
            $used = DB::table('orders')
                ->where('coupon_id', $coupon->id)
                ->where('user_id', $user->id)
                ->count();

            if ($used >= $coupon->usage_limit_per_user) {
                throw new \InvalidArgumentException('You have already used this coupon the maximum number of times.');
            }
        }

        $discount = match ($coupon->type) {
            CouponType::Percentage => (int) floor($subtotal * ((float) $coupon->value / 100)),
            CouponType::Fixed => $this->fixedDiscount($coupon, $currency),
        };

        $discount = min($discount, $subtotal);

        return ['coupon' => $coupon, 'discount' => max(0, $discount)];
    }

    private function fixedDiscount(Coupon $coupon, string $currency): int
    {
        // Fixed coupons are stored in their own currency; require a match.
        if ($coupon->currency && strtolower((string) $coupon->currency) !== $currency) {
            throw new \InvalidArgumentException("This coupon is only valid in [".strtoupper((string) $coupon->currency)."].");
        }

        return (int) round(((float) $coupon->value) * 100);
    }

    public function redeem(Coupon $coupon): void
    {
        $coupon->increment('used_count');
    }
}
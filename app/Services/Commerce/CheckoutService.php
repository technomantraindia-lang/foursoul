<?php

declare(strict_types=1);

namespace App\Services\Commerce;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Phase 5 (§2): Checkout orchestration.
 *
 * Server-side recalculation only — frontend totals are never trusted.
 * Flow: Product → Checkout → Order → Payment → Invoice →
 * SubscriptionService::activate() → Entitlement → Enrollment → Course Access.
 */
class CheckoutService
{
    public function __construct(
        private ProductResolver $products,
        private CouponService $coupons,
        private OrderNumberGenerator $orderNumbers,
    ) {
    }

    /**
     * Preview a checkout (order summary) without creating an order.
     *
     * @param array{product_type:string,product_id:int|string,quantity?:int,coupon_code?:?string} $input
     * @return array{product:array,quantity:int,subtotal:int,discount:int,tax:int,total:int,currency:string,coupon:?array}
     */
    public function preview(User $user, array $input): array
    {
        return $this->calculate($user, $input);
    }

    /**
     * Create the order from server-side recalculated totals.
     */
    public function createOrder(User $user, array $input, string $source = 'web'): Order
    {
        $calc = $this->calculate($user, $input);

        return DB::transaction(function () use ($user, $input, $calc, $source) {
            /** @var Order $order */
            $order = Order::create([
                'uuid' => (string) \Illuminate\Support\Str::uuid(),
                'order_number' => $this->orderNumbers->generate(),
                'user_id' => $user->id,
                'currency' => $calc['currency'],
                'subtotal' => $calc['subtotal'],
                'discount' => $calc['discount'],
                'tax' => $calc['tax'],
                'total' => $calc['total'],
                'status' => OrderStatus::Pending,
                'payment_status' => PaymentStatus::Pending,
                'coupon_id' => $calc['coupon']['id'] ?? null,
                'billing_information' => $input['billing'] ?? null,
                'source' => $source,
                'metadata' => [
                    'product_type' => $input['product_type'],
                    'product_id' => $calc['product']['purchasable']->getKey(),
                ],
            ]);

            $order->items()->create([
                'uuid' => (string) \Illuminate\Support\Str::uuid(),
                'product_type' => $input['product_type'],
                'product_id' => $calc['product']['purchasable']->getKey(),
                'quantity' => $calc['quantity'],
                'unit_price' => $calc['unit_price'],
                'discount' => $calc['discount'],
                'tax' => 0,
                'line_total' => $calc['total'],
                'currency' => $calc['currency'],
                'snapshot_name' => $calc['product']['name'],
                'snapshot_description' => $calc['product']['description'],
            ]);

            if (! empty($calc['coupon']['id'])) {
                $this->coupons->redeem(\App\Models\Coupon::find($calc['coupon']['id']));
            }

            return $order;
        });
    }

    /* -----------------------------------------------------------------
     | Internals
     * ----------------------------------------------------------------- */

    /**
     * @return array{product:array,quantity:int,unit_price:int,subtotal:int,discount:int,tax:int,total:int,currency:string,coupon:?array}
     */
    private function calculate(User $user, array $input): array
    {
        $type = (string) ($input['product_type'] ?? '');
        $id = $input['product_id'] ?? null;

        if (! in_array($type, ProductResolver::TYPES, true) || ! $id) {
            throw new \InvalidArgumentException('A valid product must be selected.');
        }

        $quantity = max(1, min(10, (int) ($input['quantity'] ?? 1)));
        $currency = strtolower((string) ($input['currency'] ?? ''));

        if (! in_array($currency, ['aed', 'usd', 'inr'], true)) {
            throw new \InvalidArgumentException('Unsupported currency.');
        }

        $product = $this->products->resolve($type, $id, $currency);
        $unitPrice = (int) round(((float) $product['unit_price']) * 100); // minor units

        $subtotal = $unitPrice * $quantity;

        $discount = 0;
        $couponData = null;

        if (! empty($input['coupon_code'])) {
            $result = $this->coupons->validate(
                (string) $input['coupon_code'],
                $user,
                $subtotal,
                $currency,
            );
            $discount = $result['discount'];
            $couponData = [
                'id' => $result['coupon']->id,
                'code' => $result['coupon']->code,
                'discount' => $discount,
            ];
        }

        // Tax hook — future tax providers plug in here. Currently zero-rated.
        $tax = 0;

        $total = max(0, $subtotal - $discount + $tax);

        return compact('product', 'quantity', 'unitPrice', 'subtotal', 'discount', 'tax', 'total', 'currency', 'couponData')
            + ['coupon' => $couponData]
            + ['unit_price' => $unitPrice];
    }
}
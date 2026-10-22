<?php

declare(strict_types=1);

namespace App\Services\Commerce;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

/**
 * Phase 5 (§1): Fulfillment — the ONLY path that activates paid content.
 *
 * Payment success → mark order paid → issue invoice →
 * SubscriptionService::activate() / Enrollment creation per order item.
 */
class FulfillmentService
{
    public function __construct(
        private InvoiceService $invoices,
    ) {
    }

    public function fulfill(Order $order, Payment $payment): Order
    {
        return DB::transaction(function () use ($order, $payment) {
            // Idempotent: never double-fulfill.
            if ($order->status === OrderStatus::Paid) {
                return $order;
            }

            $order->update([
                'status' => OrderStatus::Paid,
                'payment_status' => PaymentStatus::Succeeded,
            ]);

            $this->invoices->issueForOrder($order, $payment);

            foreach ($order->items as $item) {
                $this->activateItem($order, $item);
            }

            return $order;
        });
    }

    private function activateItem(Order $order, OrderItem $item): void
    {
        match ($item->product_type) {
            ProductResolver::TYPE_COURSE => $this->activateCourse($order, $item),
            ProductResolver::TYPE_PLAN => $this->activatePlan($order, $item),
            default => null, // future product types plug in here
        };
    }

    private function activateCourse(Order $order, OrderItem $item): void
    {
        $course = \App\Models\Course::find($item->product_id);

        if (! $course) {
            return;
        }

        \App\Models\Enrollment::firstOrCreate(
            [
                'user_id' => $order->user_id,
                'course_id' => $course->id,
            ],
            [
                'uuid' => (string) \Illuminate\Support\Str::uuid(),
                'status' => \App\Enums\EnrollmentStatus::Active,
                'source' => 'purchase',
                'enrolled_at' => now(),
                'metadata' => ['order_id' => $order->id],
            ],
        );
    }

    private function activatePlan(Order $order, OrderItem $item): void
    {
        $plan = \App\Models\SubscriptionPlan::find($item->product_id);

        if (! $plan) {
            return;
        }

        $subscription = \App\Models\UserSubscription::create([
            'user_id' => $order->user_id,
            'subscription_plan_id' => $plan->id,
            'status' => \App\Enums\SubscriptionStatus::Pending,
            'currency' => strtoupper((string) $order->currency),
            'unit_price' => $plan->effectivePrice(strtolower((string) $order->currency)) ?? 0,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => $order->total,
            'activation_source' => 'purchase',
            'source_reference' => (string) $order->id,
        ]);

        app(\App\Services\SubscriptionService::class)->activate($subscription);
    }
}
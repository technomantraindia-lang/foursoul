<?php

declare(strict_types=1);

namespace App\Services\Commerce;

use App\Models\Order;
use App\Models\Payment;

/**
 * Phase 5 (§7): Gateway-agnostic payment contract.
 *
 * Every gateway (Stripe, Razorpay, PayPal, Cash, Bank Transfer, future)
 * implements this interface — no redesign needed to add one.
 */
interface PaymentGateway
{
    /**
     * Unique gateway key (e.g. 'stripe', 'razorpay', 'cash').
     */
    public function key(): string;

    /**
     * Human-readable label.
     */
    public function label(): string;

    /**
     * Whether this gateway can be used for the given order.
     */
    public function supports(Order $order): bool;

    /**
     * Initiate a payment for an order.
     * Returns a Payment row in pending/processing state plus
     * gateway-specific payload for the frontend (redirect URL, client secret…).
     *
     * @return array{payment:Payment,payload:array}
     */
    public function initiate(Order $order): array;

    /**
     * Handle a gateway callback/webhook and return the payment
     * with its status transitioned (succeeded/failed).
     */
    public function handleCallback(Payment $payment, array $payload): Payment;
}
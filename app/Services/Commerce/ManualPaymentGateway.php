<?php

declare(strict_types=1);

namespace App\Services\Commerce;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;

/**
 * Phase 5 (§7): Offline gateway for Cash / Bank Transfer.
 *
 * Payment stays pending until an admin confirms it; the callback
 * (confirmation) then triggers fulfillment like any other gateway.
 */
class ManualPaymentGateway implements PaymentGateway
{
    public function __construct(
        private string $method = 'cash',
        private string $label = 'Cash',
    ) {
    }

    public static function cash(): self
    {
        return new self('cash', 'Cash');
    }

    public static function bankTransfer(): self
    {
        return new self('bank_transfer', 'Bank Transfer');
    }

    public function key(): string
    {
        return $this->method;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function supports(Order $order): bool
    {
        return in_array($order->status, [OrderStatus::Pending, OrderStatus::AwaitingPayment], true);
    }

    public function initiate(Order $order): array
    {
        $payment = Payment::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'order_id' => $order->id,
            'user_id' => $order->user_id,
            'gateway' => $this->method,
            'gateway_reference' => null,
            'status' => PaymentStatus::Pending,
            'currency' => $order->currency,
            'amount' => $order->total,
            'metadata' => ['instructions' => 'Awaiting offline confirmation by administrator.'],
        ]);

        return [
            'payment' => $payment,
            'payload' => [
                'type' => 'manual',
                'message' => 'Your order is recorded. Access will be activated once the payment is confirmed.',
            ],
        ];
    }

    public function handleCallback(Payment $payment, array $payload): Payment
    {
        // Admin confirmation flow: payload contains confirmed=true/false.
        if ((bool) ($payload['confirmed'] ?? false)) {
            $payment->update([
                'status' => PaymentStatus::Succeeded,
                'paid_at' => now(),
                'gateway_reference' => $payload['reference'] ?? $payment->gateway_reference,
            ]);
        } else {
            $payment->update([
                'status' => PaymentStatus::Failed,
                'failure_reason' => $payload['reason'] ?? 'Rejected by administrator.',
            ]);
        }

        return $payment->refresh();
    }
}
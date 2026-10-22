<?php

declare(strict_types=1);

namespace App\Services\Commerce;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Phase 5 (§7): Payment orchestration — gateway-independent.
 *
 * Payment success is the ONLY trigger that activates paid content
 * (via FulfillmentService → SubscriptionService::activate()).
 */
class PaymentService
{
    /** @var array<string,PaymentGateway> */
    private array $gateways = [];

    public function __construct(
        private FulfillmentService $fulfillment,
    ) {
        foreach (config('commerce.gateways', []) as $key => $class) {
            if (class_exists($class)) {
                $this->gateways[$key] = app($class);
            }
        }
    }

    /**
     * @return array<string,string> key => label of available gateways for an order
     */
    public function availableGateways(Order $order): array
    {
        $available = [];
        foreach ($this->gateways as $gateway) {
            if ($gateway->supports($order)) {
                $available[$gateway->key()] = $gateway->label();
            }
        }

        return $available;
    }

    /**
     * Initiate a payment through the chosen gateway.
     *
     * @return array{payment:Payment,payload:array}
     */
    public function initiate(Order $order, string $gatewayKey): array
    {
        $gateway = $this->resolveGateway($gatewayKey);

        if (! $gateway->supports($order)) {
            throw new \InvalidArgumentException('This payment method cannot be used for this order.');
        }

        return DB::transaction(function () use ($order, $gateway) {
            $result = $gateway->initiate($order);
            $payment = $result['payment'];

            // First initiation moves the order into awaiting_payment.
            if ($order->status === OrderStatus::Pending) {
                $order->update(['status' => OrderStatus::AwaitingPayment]);
            }

            return ['payment' => $payment, 'payload' => $result['payload']];
        });
    }

    /**
     * Handle a gateway callback/webhook.
     */
    public function handleCallback(Payment $payment, array $payload): Payment
    {
        $gateway = $this->resolveGateway($payment->gateway);

        return DB::transaction(function () use ($payment, $payload, $gateway) {
            $payment = Payment::query()->whereKey($payment->getKey())->lockForUpdate()->firstOrFail();

            // A verified callback may be delivered more than once. Once a
            // payment is terminal, never run gateway mutation or fulfillment again.
            if (in_array($payment->status, [PaymentStatus::Succeeded, PaymentStatus::Failed, PaymentStatus::Refunded, PaymentStatus::PartiallyRefunded], true)) {
                return $payment;
            }

            $payment = $gateway->handleCallback($payment, $payload);

            if ($payment->status === PaymentStatus::Succeeded) {
                $this->fulfillment->fulfill($payment->order, $payment);
            } elseif ($payment->status === PaymentStatus::Failed && $payment->order->status !== OrderStatus::Paid) {
                $payment->order->update(['status' => OrderStatus::Failed]);
            }

            return $payment;
        });
    }

    public function handleNormalizedWebhook(PaymentWebhookEvent $event): Payment
    {
        return DB::transaction(function () use ($event): Payment {
            $payment = Payment::query()->where('gateway', $event->provider)
                ->where('gateway_reference', $event->transactionId)->lockForUpdate()->first();
            if (! $payment) {
                throw ValidationException::withMessages(['webhook' => 'Payment transaction was not found.']);
            }
            if (strtoupper((string) $payment->currency) !== $event->currency || bccomp((string) $payment->amount, $event->amount, 2) !== 0) {
                throw ValidationException::withMessages(['webhook' => 'Payment amount or currency does not match the order.']);
            }
            if (in_array($payment->status, [PaymentStatus::Succeeded, PaymentStatus::Failed, PaymentStatus::Refunded, PaymentStatus::PartiallyRefunded], true)) {
                return $payment;
            }
            return $this->handleCallback($payment, ['confirmed' => in_array($event->status, ['paid', 'success', 'succeeded'], true), 'reference' => $event->transactionId, 'reason' => $event->status]);
        });
    }

    private function resolveGateway(string $key): PaymentGateway
    {
        if (! isset($this->gateways[$key])) {
            throw new \InvalidArgumentException('Unknown or disabled payment gateway.');
        }

        return $this->gateways[$key];
    }
}

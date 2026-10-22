<?php

declare(strict_types=1);

namespace App\Services\Commerce;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\Payment;

/**
 * Phase 5 (§9): Invoice issuance — one invoice per paid order.
 */
class InvoiceService
{
    public function issueForOrder(Order $order, Payment $payment): Invoice
    {
        $existing = Invoice::where('order_id', $order->id)->first();

        if ($existing) {
            return $existing; // idempotent
        }

        return Invoice::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'invoice_number' => 'INV-'.str_replace(['#', '-'], '', $order->order_number),
            'order_id' => $order->id,
            'user_id' => $order->user_id,
            'payment_id' => $payment->id,
            'currency' => $order->currency,
            'subtotal' => $order->subtotal,
            'discount' => $order->discount,
            'tax' => $order->tax,
            'total' => $order->total,
            'issued_at' => now(),
            'billing_information' => $order->billing_information,
            'metadata' => ['payment_id' => $payment->id],
        ]);
    }
}
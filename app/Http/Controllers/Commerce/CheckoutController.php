<?php

declare(strict_types=1);

namespace App\Http\Controllers\Commerce;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Commerce\CheckoutService;
use App\Services\Commerce\PaymentService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Phase 5 (§2): Authenticated checkout endpoints.
 * Totals are always recalculated server-side; frontend values are never trusted.
 */
class CheckoutController extends Controller
{
    public function __construct(
        private CheckoutService $checkout,
        private PaymentService $payments,
    ) {
    }

    public function preview(Request $request)
    {
        $data = $request->validate([
            'product_type' => ['required', 'in:course,plan'],
            'product_id' => ['required'],
            'currency' => ['required', 'string', 'size:3'],
            'coupon_code' => ['nullable', 'string'],
            'billing_information' => ['required', 'array'],
        ]);

        $quote = $this->checkout->preview($request->user(), [
            'product_type' => $data['product_type'],
            'product_id' => $data['product_id'],
            'currency' => $data['currency'],
            'coupon_code' => $data['coupon_code'] ?? null,
            'billing' => $data['billing_information'],
        ]);

        return response()->json(['quote' => $quote]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_type' => ['required', 'in:course,plan'],
            'product_id' => ['required'],
            'currency' => ['required', 'string', 'size:3'],
            'coupon_code' => ['nullable', 'string'],
            'billing_information' => ['required', 'array'],
            'payment_method' => ['required', 'string'],
        ]);

        $order = $this->checkout->createOrder($request->user(), [
            'product_type' => $data['product_type'],
            'product_id' => $data['product_id'],
            'currency' => $data['currency'],
            'coupon_code' => $data['coupon_code'] ?? null,
            'billing' => $data['billing_information'],
        ]);

        $result = $this->payments->initiate($order, $data['payment_method']);

        return redirect()
            ->route('orders.show', $order)
            ->with('checkout_payload', $result['payload']);
    }

    public function show(Request $request, Order $order): View
    {
        abort_unless((int) $order->user_id === (int) $request->user()->id, 403);

        $order->load(['items', 'payments', 'invoices']);

        return view('commerce.order-show', [
            'order' => $order,
        ]);
    }
}

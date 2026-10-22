@extends('layouts.app')

@section('title', 'Order '.$order->order_number)

@section('content')
<div class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">
    <h1 class="text-2xl font-bold text-gray-900">Order {{ $order->order_number }}</h1>
    <p class="mt-2 text-sm text-gray-600">Status: {{ $order->status->label() }} · Payment: {{ $order->payment_status->label() }}</p>

    @if (session('checkout_payload.message'))
        <p class="mt-4 rounded-lg bg-blue-50 px-4 py-3 text-sm text-blue-800">{{ session('checkout_payload.message') }}</p>
    @endif

    <div class="mt-6 overflow-hidden rounded-xl bg-white shadow">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-500">
                <tr>
                    <th class="px-4 py-3">Item</th>
                    <th class="px-4 py-3">Qty</th>
                    <th class="px-4 py-3">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->items as $item)
                    <tr class="border-t">
                        <td class="px-4 py-3">{{ $item->snapshot_name }}</td>
                        <td class="px-4 py-3">{{ $item->quantity }}</td>
                        <td class="px-4 py-3">{{ strtoupper($item->currency) }} {{ number_format(((int) $item->line_total) / 100, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@extends('layouts.app')
@section('title', 'My Subscription')
@section('content')
<div class="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:px-8">
    <p class="fs-kicker">Account</p><h1 class="mt-2 text-3xl font-bold tracking-tight text-gray-900">My subscription</h1>
    @if (! $subscription)
        <div class="fs-empty-state mt-8"><h2 class="text-lg font-semibold text-gray-900">You don't have an active subscription.</h2><a class="btn btn-primary mt-5" href="{{ route('plans.index') }}">View plans</a></div>
    @else
        @php($status = $subscription->status->label())
        <section class="fs-surface mt-8 p-6 sm:p-8"><div class="flex flex-wrap items-start justify-between gap-4"><div><p class="text-sm text-gray-500">Current plan</p><h2 class="mt-1 text-2xl font-semibold text-gray-900">{{ $subscription->plan?->name ?? 'Subscription' }}</h2></div><span class="fs-badge {{ $subscription->grantsAccess() ? 'fs-badge-success' : 'fs-badge-danger' }}">{{ strtoupper($status) }}</span></div>
            <dl class="mt-8 grid gap-5 sm:grid-cols-2"><div><dt class="text-sm text-gray-500">Start date</dt><dd class="mt-1 font-semibold text-gray-900">{{ $subscription->started_at?->format('M j, Y') ?? 'Not started' }}</dd></div><div><dt class="text-sm text-gray-500">Expiry date</dt><dd class="mt-1 font-semibold text-gray-900">{{ $subscription->expires_at?->format('M j, Y') ?? 'Lifetime access' }}</dd></div><div><dt class="text-sm text-gray-500">Currency</dt><dd class="mt-1 font-semibold text-gray-900">{{ strtoupper($subscription->currency) }}</dd></div><div><dt class="text-sm text-gray-500">Amount</dt><dd class="mt-1 font-semibold text-gray-900">{{ \App\Support\CurrencyFormatter::formatCode($subscription->currency, $subscription->total_amount) }}</dd></div></dl>
            <div class="mt-8 flex flex-wrap gap-3"><a class="btn btn-secondary" href="{{ route('learn.index') }}">My learning</a>@if (! $subscription->grantsAccess())<a class="btn btn-primary" href="{{ route('subscription.renew') }}">Renew access</a>@endif</div>
        </section>
    @endif
</div>
@endsection

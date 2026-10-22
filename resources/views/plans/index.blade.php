@extends('layouts.app')
@section('title', 'Plans')
@section('content')
<div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
    <div class="max-w-2xl"><p class="fs-kicker">Choose your access</p><h1 class="mt-2 text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">Subscription plans</h1><p class="mt-3 text-base text-gray-600">Select the plan that matches how you want to learn.</p></div>
    @if ($plans->isEmpty())
        <div class="fs-empty-state mt-10"><h2 class="text-lg font-semibold text-gray-900">No subscription plans are currently available.</h2></div>
    @else
        <div class="mt-10 grid gap-6 lg:grid-cols-3">
            @foreach ($plans as $plan)
                <article class="fs-plan-card {{ $plan->id === $currentPlanId ? 'fs-plan-current' : '' }}">
                    @if ($plan->id === $currentPlanId)<div class="fs-plan-ribbon">Current plan</div>@endif
                    <p class="fs-kicker">{{ $plan->billing_type->label() }}</p><h2 class="mt-2 text-2xl font-semibold text-gray-900">{{ $plan->name }}</h2>
                    <p class="mt-3 min-h-12 text-sm leading-6 text-gray-600">{{ $plan->short_description ?: $plan->description }}</p>
                    <p class="mt-6 text-3xl font-bold text-gray-900">{{ $plan->effectivePrice($currency->value) !== null ? \App\Support\CurrencyFormatter::format($currency, $plan->effectivePrice($currency->value)) : 'Price unavailable' }}</p>
                    <p class="mt-1 text-sm text-gray-500">{{ $plan->billing_type->label() }} · {{ $plan->courses_count }} course{{ $plan->courses_count === 1 ? '' : 's' }}</p>
                    <div class="mt-8 rounded-lg bg-gray-50 p-4 text-sm text-gray-700">Live payment provider setup is pending. No payment has been initiated.</div>
                    <button type="button" class="btn btn-secondary btn-block mt-5" disabled aria-disabled="true">Purchase unavailable</button>
                </article>
            @endforeach
        </div>
    @endif
</div>
@endsection

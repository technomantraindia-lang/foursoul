@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
    {{-- Page heading --}}
    <div class="md:flex md:items-end md:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl">
                Welcome, {{ $user->full_name }}
            </h1>
            <p class="mt-2 text-sm text-gray-600">
                Keep your profile, preferences, and learning journey in one place.
            </p>
        </div>
        <div class="mt-4 flex flex-wrap gap-3 md:mt-0">
            <a href="{{ route('learn.index') }}"
               class="inline-flex items-center rounded-lg bg-orange-500 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-orange-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orange-600">
                Go to My Learning
            </a>
            <a href="{{ route('courses.index') }}" class="inline-flex items-center rounded-lg bg-white px-4 py-2.5 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Browse Courses</a>
            <a href="{{ route('subscription.show') }}" class="inline-flex items-center rounded-lg bg-white px-4 py-2.5 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">My Subscription</a>
            <a href="{{ route('account.profile.edit') }}"
               class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                Edit profile
            </a>
            <a href="{{ route('account.security.edit') }}"
               class="inline-flex items-center rounded-lg bg-white px-4 py-2.5 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                Security
            </a>
            <a href="{{ route('account.preferences.edit') }}"
               class="inline-flex items-center rounded-lg bg-white px-4 py-2.5 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                Preferences
            </a>
        </div>
    </div>

    <x-flash-message/>

    {{-- Account overview cards --}}
    <dl class="mt-8 grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3">
        {{-- Account status --}}
        <div class="fs-surface overflow-hidden">
            <div class="px-6 py-5">
                <dt class="text-sm font-medium leading-6 text-gray-600">Account status</dt>
                <dd class="mt-2 flex items-center gap-2">
                    @if ($user->status === \App\Enums\UserStatus::Active)
                        <span class="inline-flex items-center gap-x-1.5 rounded-md bg-green-50 px-2 py-1 text-xs font-semibold text-green-700 ring-1 ring-inset ring-green-600/20">
                            <span class="h-1.5 w-1.5 rounded-full bg-green-500" aria-hidden="true"></span> Active
                        </span>
                    @elseif ($user->status === \App\Enums\UserStatus::Suspended)
                        <span class="inline-flex items-center gap-x-1.5 rounded-md bg-amber-50 px-2 py-1 text-xs font-semibold text-amber-700 ring-1 ring-inset ring-amber-600/20">
                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500" aria-hidden="true"></span> Suspended
                        </span>
                    @elseif ($user->status === \App\Enums\UserStatus::Blocked)
                        <span class="inline-flex items-center gap-x-1.5 rounded-md bg-red-50 px-2 py-1 text-xs font-semibold text-red-700 ring-1 ring-inset ring-red-600/20">
                            <span class="h-1.5 w-1.5 rounded-full bg-red-500" aria-hidden="true"></span> Blocked
                        </span>
                    @else
                        <span class="inline-flex items-center gap-x-1.5 rounded-md bg-gray-100 px-2 py-1 text-xs font-semibold text-gray-700 ring-1 ring-inset ring-gray-500/20">
                            <span class="h-1.5 w-1.5 rounded-full bg-gray-400" aria-hidden="true"></span> Closed
                        </span>
                    @endif
                </dd>
            </div>
        </div>

        {{-- Email verification --}}
        <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-900/5">
            <div class="px-6 py-5">
                <dt class="text-sm font-medium leading-6 text-gray-600">Email verification</dt>
                <dd class="mt-2 flex items-center justify-between gap-3">
                    @if ($user->hasVerifiedEmail())
                        <span class="inline-flex items-center gap-x-1.5 rounded-md bg-green-50 px-2 py-1 text-xs font-semibold text-green-700 ring-1 ring-inset ring-green-600/20">
                            Verified
                        </span>
                    @else
                        <span class="inline-flex items-center gap-x-1.5 rounded-md bg-amber-50 px-2 py-1 text-xs font-semibold text-amber-700 ring-1 ring-inset ring-amber-600/20">
                            Pending
                        </span>
                        <a href="{{ route('verification.notice') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-700">Verify now</a>
                    @endif
                </dd>
            </div>
        </div>

        {{-- Mobile verification --}}
        <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-900/5">
            <div class="px-6 py-5">
                <dt class="text-sm font-medium leading-6 text-gray-600">Mobile verification</dt>
                <dd class="mt-2">
                    @if ($user->mobile_verified_at)
                        <span class="inline-flex items-center gap-x-1.5 rounded-md bg-green-50 px-2 py-1 text-xs font-semibold text-green-700 ring-1 ring-inset ring-green-600/20">
                            Verified
                        </span>
                    @elseif ($user->mobile_number)
                        <span class="inline-flex items-center gap-x-1.5 rounded-md bg-amber-50 px-2 py-1 text-xs font-semibold text-amber-700 ring-1 ring-inset ring-amber-600/20">
                            Not verified yet
                        </span>
                    @else
                        <span class="text-sm text-gray-500">No mobile number on file</span>
                    @endif
                </dd>
            </div>
        </div>

        {{-- Preferred currency --}}
        <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-900/5">
            <div class="px-6 py-5">
                <dt class="text-sm font-medium leading-6 text-gray-600">Preferred currency</dt>
                <dd class="mt-2 text-lg font-semibold text-gray-900">{{ $activeCurrency?->label() ?? 'USD' }} ({{ ($activeCurrency ?? \App\Support\Currency::USD)->value }})</dd>
            </div>
        </div>

        {{-- Preferred language --}}
        <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-900/5">
            <div class="px-6 py-5">
                <dt class="text-sm font-medium leading-6 text-gray-600">Preferred language</dt>
                <dd class="mt-2 text-lg font-semibold text-gray-900">{{ \App\Support\Locales::isValid($user->preferred_language) ? strtoupper($user->preferred_language) : 'EN' }}</dd>
            </div>
        </div>

        {{-- Member since --}}
        <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-900/5">
            <div class="px-6 py-5">
                <dt class="text-sm font-medium leading-6 text-gray-600">Member since</dt>
                <dd class="mt-2 text-lg font-semibold text-gray-900">{{ $user->created_at?->translatedFormat('M j, Y') }}</dd>
            </div>
        </div>
    </dl>

    {{-- Customer learning workspace --}}
    <section class="mt-10" aria-labelledby="learning-heading">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="fs-kicker">Your member space</p>
                <h2 id="learning-heading" class="mt-1 text-xl font-semibold leading-7 text-gray-900">Keep your practice moving</h2>
            </div>
            <a href="{{ route('learn.index') }}" class="text-sm font-semibold text-blue-700 hover:text-blue-800">Open My Learning →</a>
        </div>
        <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <a href="{{ route('learn.index') }}" class="fs-surface block p-5 transition hover:-translate-y-0.5 hover:border-blue-300">
                <span class="text-2xl" aria-hidden="true">◈</span>
                <h3 class="mt-3 font-semibold text-gray-900">My Learning</h3>
                <p class="mt-1 text-sm leading-5 text-gray-600">Continue enrolled courses and track lesson progress.</p>
            </a>
            <a href="{{ route('learn.live-sessions.index') }}" class="fs-surface block p-5 transition hover:-translate-y-0.5 hover:border-orange-300">
                <span class="text-2xl" aria-hidden="true">◉</span>
                <h3 class="mt-3 font-semibold text-gray-900">Live Sessions</h3>
                <p class="mt-1 text-sm leading-5 text-gray-600">See your schedule, join the live room, and watch replays.</p>
            </a>
            <a href="{{ route('certificates.index') }}" class="fs-surface block p-5 transition hover:-translate-y-0.5 hover:border-green-300">
                <span class="text-2xl" aria-hidden="true">◇</span>
                <h3 class="mt-3 font-semibold text-gray-900">Certificates</h3>
                <p class="mt-1 text-sm leading-5 text-gray-600">View certificates issued to your account and verify them.</p>
            </a>
            <a href="{{ route('courses.index') }}" class="fs-surface block p-5 transition hover:-translate-y-0.5 hover:border-violet-300">
                <span class="text-2xl" aria-hidden="true">＋</span>
                <h3 class="mt-3 font-semibold text-gray-900">Browse Courses</h3>
                <p class="mt-1 text-sm leading-5 text-gray-600">Find your next course and unlock your learning path.</p>
            </a>
        </div>
    </section>
</div>
@endsection

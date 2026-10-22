@extends('layouts.app')

@section('title', 'Account status')

@section('content')
<div class="flex min-h-full flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-lg">
        {{-- Brand region --}}
        <div class="flex flex-col items-center">
            <a href="{{ route('home') }}" class="flex items-center gap-2" aria-label="{{ config('app.name', 'Foursouls') }} home">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-blue-600 to-blue-800 text-lg font-bold text-white shadow-md">FS</span>
                <span class="text-xl font-bold tracking-tight text-gray-900">{{ config('app.name', 'Foursouls') }}</span>
            </a>
        </div>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-lg">
        <div class="bg-white px-4 py-8 shadow-xl shadow-blue-900/5 ring-1 ring-gray-900/5 sm:rounded-2xl sm:px-10">
            @if ($status === \App\Enums\UserStatus::Suspended)
                {{-- Suspended --}}
                <div class="flex items-start gap-4">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-amber-100" aria-hidden="true">
                        <svg class="h-6 w-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                        </svg>
                    </span>
                    <div>
                        <h1 class="text-xl font-bold tracking-tight text-gray-900">Account suspended</h1>
                        <p class="mt-2 text-sm leading-6 text-gray-600">
                            Your account is currently suspended. Your account history has been preserved, but protected features are temporarily restricted.
                        </p>
                        <p class="mt-2 text-sm leading-6 text-gray-600">
                            If you believe this is a mistake, please contact support.
                        </p>
                    </div>
                </div>
            @elseif ($status === \App\Enums\UserStatus::Blocked)
                {{-- Blocked --}}
                <div class="flex items-start gap-4">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-red-100" aria-hidden="true">
                        <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                        </svg>
                    </span>
                    <div>
                        <h1 class="text-xl font-bold tracking-tight text-gray-900">Account blocked</h1>
                        <p class="mt-2 text-sm leading-6 text-gray-600">
                            Access to this account has been denied. If you believe this was done in error, please contact support for assistance.
                        </p>
                    </div>
                </div>
            @else
                {{-- Closed --}}
                <div class="flex items-start gap-4">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-gray-100" aria-hidden="true">
                        <svg class="h-6 w-6 text-gray-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
                        </svg>
                    </span>
                    <div>
                        <h1 class="text-xl font-bold tracking-tight text-gray-900">Account closed</h1>
                        <p class="mt-2 text-sm leading-6 text-gray-600">
                            This account has been closed and normal access is disabled. Account records are retained in line with our data policy.
                        </p>
                        <p class="mt-2 text-sm leading-6 text-gray-600">
                            Need to reopen your account? Contact support.
                        </p>
                    </div>
                </div>
            @endif

            {{-- Safe info only --}}
            <dl class="mt-8 space-y-4 border-t border-gray-200 pt-6">
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-sm font-medium text-gray-500">Account email</dt>
                    <dd class="text-sm font-semibold text-gray-900">{{ $user->email }}</dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-sm font-medium text-gray-500">Member since</dt>
                    <dd class="text-sm font-semibold text-gray-900">{{ $user->created_at?->translatedFormat('M j, Y') }}</dd>
                </div>
            </dl>

            {{-- Logout --}}
            <form method="POST" action="{{ route('logout') }}" class="mt-8" novalidate>
                @csrf
                <button type="submit"
                        class="flex w-full justify-center rounded-lg bg-white px-3 py-2.5 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                    Log out
                </button>
            </form>

            <p class="mt-6 text-center text-xs leading-5 text-gray-500">
                Support: {{ config('legal.support_email', 'support@example.com') }}
            </p>
        </div>
    </div>
</div>
@endsection
@extends('layouts.app')

@section('title', 'Verify your email')

@section('content')
<div class="auth-page flex min-h-full flex-col justify-center px-4 py-14 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        {{-- Brand region --}}
        <div class="flex flex-col items-center">
            <a href="{{ route('home') }}" class="flex items-center gap-2" aria-label="{{ config('app.name', 'Foursouls') }} home">
                <span class="brand-mark" aria-hidden="true">FS</span>
                <span class="text-xl font-bold tracking-tight text-gray-900">{{ config('app.name', 'Foursouls') }}</span>
            </a>
            <h1 class="mt-6 text-center text-2xl font-bold tracking-tight text-gray-900">Verify your email address</h1>
            <p class="mt-2 text-center text-sm text-gray-600">
                We sent a verification link to
                <strong class="font-semibold text-gray-900">{{ auth()->user()?->email }}</strong>.
                Click the link in that email to activate full account access.
            </p>
        </div>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="auth-panel rounded-2xl px-4 py-8 sm:px-10">
            <x-flash-message/>

            @if (session('status') === 'verification-link-sent')
                <div class="mb-6 rounded-lg bg-green-50 p-4 ring-1 ring-inset ring-green-600/20" role="status">
                    <p class="text-sm leading-6 text-green-800">
                        A fresh verification link has been sent to your email address.
                    </p>
                </div>
            @endif

            <div class="space-y-4">
                {{-- Resend verification --}}
                <form method="POST" action="{{ route('verification.send') }}" novalidate>
                    @csrf
                    <button type="submit"
                            class="auth-submit flex w-full justify-center px-3 py-2.5 text-sm font-semibold text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 disabled:cursor-not-allowed disabled:opacity-60">
                        Resend verification email
                    </button>
                </form>

                {{-- Refresh after clicking the emailed link --}}
                <form method="GET" action="{{ route('verification.notice') }}" novalidate>
                    <button type="submit"
                            class="flex w-full justify-center rounded-lg bg-white px-3 py-2.5 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 disabled:cursor-not-allowed disabled:opacity-60">
                        I have verified — continue
                    </button>
                </form>

                {{-- Logout --}}
                <form method="POST" action="{{ route('logout') }}" novalidate>
                    @csrf
                    <button type="submit"
                            class="flex w-full justify-center rounded-lg px-3 py-2.5 text-sm font-semibold text-gray-600 hover:text-gray-900 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 disabled:cursor-not-allowed disabled:opacity-60">
                        Log out
                    </button>
                </form>
            </div>

            <p class="mt-6 text-center text-xs leading-5 text-gray-500">
                Wrong address or cannot access the email? Contact support for help updating it.
            </p>
        </div>
    </div>
</div>
@endsection

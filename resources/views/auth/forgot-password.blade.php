@extends('layouts.app')

@section('title', 'Forgot password')

@section('content')
<div class="auth-page flex min-h-full flex-col justify-center px-4 py-14 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        {{-- Brand region --}}
        <div class="flex flex-col items-center">
            <a href="{{ route('home') }}" class="flex items-center gap-2" aria-label="{{ config('app.name', 'Foursouls') }} home">
                <span class="brand-mark" aria-hidden="true">FS</span>
                <span class="text-xl font-bold tracking-tight text-gray-900">{{ config('app.name', 'Foursouls') }}</span>
            </a>
            <h1 class="mt-6 text-center text-2xl font-bold tracking-tight text-gray-900">Reset your password</h1>
            <p class="mt-2 text-center text-sm text-gray-600">
                Enter your account email and we will send you a secure reset link.
            </p>
        </div>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="auth-panel rounded-2xl px-4 py-8 sm:px-10">
            <x-flash-message/>

            @if (session('status'))
                <div class="mb-6 rounded-lg bg-green-50 p-4 ring-1 ring-inset ring-green-600/20" role="status">
                    <p class="text-sm leading-6 text-green-800">{{ session('status') }}</p>
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="space-y-6" novalidate>
                @csrf

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-sm font-medium leading-6 text-gray-900">
                        Email address <span class="text-red-500" aria-hidden="true">*</span>
                    </label>
                    <div class="mt-2">
                        <input id="email" name="email" type="email" autocomplete="email" required value="{{ old('email') }}"
                               aria-required="true" {{ $errors->has('email') ? 'aria-invalid="true" aria-describedby="email-error"' : '' }}
                               class="auth-field block w-full px-3 py-2.5 text-gray-900 placeholder:text-gray-400 sm:text-sm sm:leading-6 {{ $errors->has('email') ? 'ring-red-300' : 'ring-gray-300' }}">
                    </div>
                    @error('email')
                        <p id="email-error" class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <button type="submit"
                            class="auth-submit flex w-full justify-center px-3 py-2.5 text-sm font-semibold text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 disabled:cursor-not-allowed disabled:opacity-60">
                        Send reset link
                    </button>
                </div>
            </form>

            <p class="mt-6 text-center text-sm text-gray-600">
                Remembered it?
                <a href="{{ route('login') }}" class="font-semibold text-blue-600 hover:text-blue-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">Back to sign in</a>
            </p>
        </div>
    </div>
</div>
@endsection

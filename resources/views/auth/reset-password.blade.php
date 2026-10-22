@extends('layouts.app')

@section('title', 'Set a new password')

@section('content')
<div class="auth-page flex min-h-full flex-col justify-center px-4 py-14 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        {{-- Brand region --}}
        <div class="flex flex-col items-center">
            <a href="{{ route('home') }}" class="flex items-center gap-2" aria-label="{{ config('app.name', 'Foursouls') }} home">
                <span class="brand-mark" aria-hidden="true">FS</span>
                <span class="text-xl font-bold tracking-tight text-gray-900">{{ config('app.name', 'Foursouls') }}</span>
            </a>
            <h1 class="mt-6 text-center text-2xl font-bold tracking-tight text-gray-900">Choose a new password</h1>
            <p class="mt-2 text-center text-sm text-gray-600">
                Create a strong password you have not used elsewhere.
            </p>
        </div>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="auth-panel rounded-2xl px-4 py-8 sm:px-10">
            <x-flash-message/>

            <form method="POST" action="{{ route('password.update') }}" class="space-y-6" novalidate>
                @csrf

                {{-- Token (hidden) --}}
                <input type="hidden" name="token" value="{{ request()->route('token') }}">

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-sm font-medium leading-6 text-gray-900">
                        Email address <span class="text-red-500" aria-hidden="true">*</span>
                    </label>
                    <div class="mt-2">
                        <input id="email" name="email" type="email" autocomplete="email" required value="{{ old('email', request()->query('email')) }}"
                               aria-required="true" {{ $errors->has('email') ? 'aria-invalid="true" aria-describedby="email-error"' : '' }}
                               class="auth-field block w-full px-3 py-2.5 text-gray-900 placeholder:text-gray-400 sm:text-sm sm:leading-6 {{ $errors->has('email') ? 'ring-red-300' : 'ring-gray-300' }}">
                    </div>
                    @error('email')
                        <p id="email-error" class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- New password --}}
                <div>
                    <label for="password" class="block text-sm font-medium leading-6 text-gray-900">
                        New Password <span class="text-red-500" aria-hidden="true">*</span>
                    </label>
                    <div class="relative mt-2">
                        <input id="password" name="password" type="password" autocomplete="new-password" required data-password-toggle
                               aria-required="true" aria-describedby="password-hint" {{ $errors->has('password') ? 'aria-invalid="true" aria-describedby="password-error password-hint"' : '' }}
                               class="auth-field block w-full px-3 py-2.5 pr-16 text-gray-900 placeholder:text-gray-400 sm:text-sm sm:leading-6 {{ $errors->has('password') ? 'ring-red-300' : 'ring-gray-300' }}">
                        <button type="button" data-toggle-target="password" data-toggle-state="hidden"
                                class="absolute inset-y-0 right-0 flex items-center px-3 text-xs font-semibold text-blue-600 hover:text-blue-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600"
                                aria-label="Show new password">Show</button>
                    </div>
                    <p id="password-hint" class="mt-2 text-xs text-gray-500">At least 8 characters, with letters and numbers.</p>
                    @error('password')
                        <p id="password-error" class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Confirm password --}}
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium leading-6 text-gray-900">
                        Confirm Password <span class="text-red-500" aria-hidden="true">*</span>
                    </label>
                    <div class="relative mt-2">
                        <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required data-password-toggle
                               aria-required="true" {{ $errors->has('password_confirmation') ? 'aria-invalid="true" aria-describedby="password_confirmation-error"' : '' }}
                               class="auth-field block w-full px-3 py-2.5 pr-16 text-gray-900 placeholder:text-gray-400 sm:text-sm sm:leading-6 {{ $errors->has('password_confirmation') ? 'ring-red-300' : 'ring-gray-300' }}">
                        <button type="button" data-toggle-target="password_confirmation" data-toggle-state="hidden"
                                class="absolute inset-y-0 right-0 flex items-center px-3 text-xs font-semibold text-blue-600 hover:text-blue-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600"
                                aria-label="Show password confirmation">Show</button>
                    </div>
                    @error('password_confirmation')
                        <p id="password_confirmation-error" class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <button type="submit"
                            class="auth-submit flex w-full justify-center px-3 py-2.5 text-sm font-semibold text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 disabled:cursor-not-allowed disabled:opacity-60">
                        Reset password
                    </button>
                </div>
            </form>

            <p class="mt-6 text-center text-sm text-gray-600">
                Link expired or invalid?
                <a href="{{ route('password.request') }}" class="font-semibold text-blue-600 hover:text-blue-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">Request a new one</a>
            </p>
        </div>
    </div>
</div>
@endsection

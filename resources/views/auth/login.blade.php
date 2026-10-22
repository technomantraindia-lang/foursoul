@extends('layouts.app')

@section('title', 'Sign in')

@section('content')
<div class="auth-page flex min-h-full flex-col justify-center px-4 py-14 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        {{-- Brand region --}}
        <div class="flex flex-col items-center">
            <a href="{{ route('home') }}" class="flex items-center gap-2" aria-label="{{ config('app.name', 'Foursouls') }} home">
                <span class="brand-mark" aria-hidden="true">FS</span>
                <span class="text-xl font-bold tracking-tight text-gray-900">{{ config('app.name', 'Foursouls') }}</span>
            </a>
            <h1 class="mt-6 text-center text-2xl font-bold tracking-tight text-gray-900">Welcome back</h1>
            <p class="mt-2 text-center text-sm text-gray-600">
                New here?
                <a href="{{ route('register') }}" class="font-semibold text-blue-600 hover:text-blue-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">Create an account</a>
            </p>
        </div>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="auth-panel rounded-2xl px-5 py-8 sm:px-10">
            <x-flash-message/>

            <form method="POST" action="{{ route('login.store') }}" class="space-y-6" novalidate>
                @csrf

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-sm font-medium leading-6 text-gray-900">
                        Email address <span class="text-red-500" aria-hidden="true">*</span>
                    </label>
                    <div class="mt-2">
                        <input id="email" name="email" type="email" autocomplete="email" required value="{{ old('email') }}"
                               aria-required="true" {{ $errors->has('email') ? 'aria-invalid="true" aria-describedby="email-error"' : '' }}
                               class="auth-field block w-full px-3 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6 {{ $errors->has('email') ? 'ring-red-300' : 'ring-gray-300' }}">
                    </div>
                    @error('email')
                        <p id="email-error" class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password --}}
                <div>
                    <label for="password" class="block text-sm font-medium leading-6 text-gray-900">
                        Password <span class="text-red-500" aria-hidden="true">*</span>
                    </label>
                    <div class="relative mt-2">
                        <input id="password" name="password" type="password" autocomplete="current-password" required
                               aria-required="true" {{ $errors->has('password') ? 'aria-invalid="true" aria-describedby="password-error"' : '' }}
                               class="auth-field block w-full px-3 py-2.5 pr-16 text-gray-900 shadow-sm ring-1 ring-inset placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6 {{ $errors->has('password') ? 'ring-red-300' : 'ring-gray-300' }}">
                        <button type="button" data-password-toggle="password" data-toggle-state="hidden"
                                class="absolute inset-y-0 right-0 flex items-center px-3 text-xs font-semibold text-blue-600 hover:text-blue-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600"
                                aria-label="Show password">Show</button>
                    </div>
                    @error('password')
                        <p id="password-error" class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Remember me + forgot password --}}
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <input id="remember" name="remember" type="checkbox" value="1"
                               {{ old('remember') ? 'checked' : '' }}
                               class="h-4 w-4 shrink-0 rounded border-gray-300 text-blue-600 focus:ring-blue-600">
                        <label for="remember" class="text-sm leading-6 text-gray-700">Remember me</label>
                    </div>
                    <a href="{{ route('password.request') }}"
                       class="text-sm font-semibold text-blue-600 hover:text-blue-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                        Forgot password?
                    </a>
                </div>

                <div>
                    <button type="submit"
                            class="auth-submit flex w-full justify-center px-3 py-2.5 text-sm font-semibold text-white shadow-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 disabled:cursor-not-allowed disabled:opacity-60">
                        Sign in
                    </button>
                </div>
            </form>
        </div>

        {{-- Side panel messaging --}}
        <div class="mt-8 grid grid-cols-2 gap-3 sm:max-w-md sm:mx-auto">
            <div class="auth-feature-card rounded-xl p-4">
                <p class="text-sm font-semibold text-gray-900">Learn at your pace</p>
                <p class="mt-1 text-xs leading-5 text-gray-500">Study whenever it suits you.</p>
            </div>
            <div class="auth-feature-card rounded-xl p-4">
                <p class="text-sm font-semibold text-gray-900">Join live sessions</p>
                <p class="mt-1 text-xs leading-5 text-gray-500">Interactive classes with instructors.</p>
            </div>
            <div class="auth-feature-card rounded-xl p-4">
                <p class="text-sm font-semibold text-gray-900">Track progress</p>
                <p class="mt-1 text-xs leading-5 text-gray-500">See how far you have come.</p>
            </div>
            <div class="auth-feature-card rounded-xl p-4">
                <p class="text-sm font-semibold text-gray-900">Continue where you stopped</p>
                <p class="mt-1 text-xs leading-5 text-gray-500">Pick up right from your last lesson.</p>
            </div>
        </div>
    </div>
</div>
@endsection

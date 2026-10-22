@extends('layouts.app')

@section('title', __('Security'))

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Page heading --}}
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900 sm:text-3xl">{{ __('Security') }}</h1>
        <p class="mt-2 text-sm text-gray-600">{{ __('Manage your password and keep your account secure.') }}</p>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Change password form --}}
        <div class="lg:col-span-2">
            <form method="POST" action="{{ route('account.security.password') }}" class="bg-white shadow-sm ring-1 ring-gray-900/5 rounded-xl sm:rounded-2xl" x-data="{ showCurrent: false, showNew: false, showConfirm: false }">
                @csrf
                @method('PUT')

                <div class="px-4 py-5 sm:p-6 space-y-6">
                    <div>
                        <h2 class="text-base font-semibold leading-7 text-gray-900">{{ __('Change Password') }}</h2>
                        <p class="mt-1 text-sm text-gray-500">{{ __('Choose a strong password of at least 12 characters. You will remain signed in on this device after changing it.') }}</p>
                    </div>

                    {{-- Current password --}}
                    <div>
                        <label for="current_password" class="block text-sm font-medium leading-6 text-gray-900">
                            {{ __('Current Password') }} <span class="text-red-500">*</span>
                        </label>
                        <div class="mt-2 relative rounded-lg shadow-sm">
                            <input :type="showCurrent ? 'text' : 'password'" id="current_password" name="current_password" required autocomplete="current-password"
                                class="block w-full rounded-lg border-0 pr-10 px-3 py-2 text-gray-900 ring-1 ring-inset focus:ring-2 focus:ring-inset sm:text-sm sm:leading-6 {{ $errors->has('current_password') ? 'ring-red-300 focus:ring-red-500' : 'ring-gray-300 focus:ring-blue-600' }}">
                            <button type="button" @click="showCurrent = !showCurrent"
                                class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 hover:text-gray-600 focus:outline-none focus-visible:text-gray-700"
                                :aria-label="showCurrent ? '{{ __('Hide current password') }}' : '{{ __('Show current password') }}'"
                                aria-controls="current_password">
                                <svg x-show="!showCurrent" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <svg x-show="showCurrent" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                            </button>
                        </div>
                        @error('current_password')
                            <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- New password --}}
                    <div>
                        <label for="password" class="block text-sm font-medium leading-6 text-gray-900">
                            {{ __('New Password') }} <span class="text-red-500">*</span>
                        </label>
                        <div class="mt-2 relative rounded-lg shadow-sm">
                            <input :type="showNew ? 'text' : 'password'" id="password" name="password" required autocomplete="new-password"
                                class="block w-full rounded-lg border-0 pr-10 px-3 py-2 text-gray-900 ring-1 ring-inset focus:ring-2 focus:ring-inset sm:text-sm sm:leading-6 {{ $errors->has('password') ? 'ring-red-300 focus:ring-red-500' : 'ring-gray-300 focus:ring-blue-600' }}">
                            <button type="button" @click="showNew = !showNew"
                                class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 hover:text-gray-600 focus:outline-none focus-visible:text-gray-700"
                                :aria-label="showNew ? '{{ __('Hide new password') }}' : '{{ __('Show new password') }}'"
                                aria-controls="password">
                                <svg x-show="!showNew" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <svg x-show="showNew" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                            </button>
                        </div>
                        <p class="mt-2 text-xs text-gray-500">{{ __('At least 12 characters, with uppercase and lowercase letters, a number, and a symbol.') }}</p>
                        @error('password')
                            <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Confirm new password --}}
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium leading-6 text-gray-900">
                            {{ __('Confirm New Password') }} <span class="text-red-500">*</span>
                        </label>
                        <div class="mt-2 relative rounded-lg shadow-sm">
                            <input :type="showConfirm ? 'text' : 'password'" id="password_confirmation" name="password_confirmation" required autocomplete="new-password"
                                class="block w-full rounded-lg border-0 pr-10 px-3 py-2 text-gray-900 ring-1 ring-inset focus:ring-2 focus:ring-inset sm:text-sm sm:leading-6 {{ $errors->has('password_confirmation') ? 'ring-red-300 focus:ring-red-500' : 'ring-gray-300 focus:ring-blue-600' }}">
                            <button type="button" @click="showConfirm = !showConfirm"
                                class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 hover:text-gray-600 focus:outline-none focus-visible:text-gray-700"
                                :aria-label="showConfirm ? '{{ __('Hide confirmation password') }}' : '{{ __('Show confirmation password') }}'"
                                aria-controls="password_confirmation">
                                <svg x-show="!showConfirm" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <svg x-show="showConfirm" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                            </button>
                        </div>
                        @error('password_confirmation')
                            <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Form actions --}}
                <div class="flex items-center justify-end gap-3 border-t border-gray-200 px-4 py-4 sm:px-6 rounded-b-xl sm:rounded-b-2xl bg-gray-50">
                    <a href="{{ route('dashboard') }}" class="rounded-lg px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500">
                        {{ __('Cancel') }}
                    </a>
                    <button type="submit"
                        class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 disabled:opacity-50 disabled:pointer-events-none">
                        {{ __('Update Password') }}
                    </button>
                </div>
            </form>
        </div>

        {{-- Security tips sidebar --}}
        <aside class="space-y-6" aria-label="{{ __('Security Tips') }}">
            <div class="bg-white shadow-sm ring-1 ring-gray-900/5 rounded-xl sm:rounded-2xl p-5 sm:p-6">
                <h2 class="text-sm font-semibold text-gray-900">{{ __('Good Password Habits') }}</h2>
                <ul class="mt-4 space-y-3 text-sm text-gray-600 list-disc list-inside">
                    <li>{{ __('Use a unique password you do not use anywhere else.') }}</li>
                    <li>{{ __('Avoid personal details such as names or birthdays.') }}</li>
                    <li>{{ __('Consider using a reputable password manager.') }}</li>
                </ul>
            </div>
        </aside>
    </div>
</div>
@endsection
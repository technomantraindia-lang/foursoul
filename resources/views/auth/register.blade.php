@extends('layouts.app')

@section('title', 'Create your account')

@section('content')
<div class="auth-page flex min-h-full flex-col justify-center px-4 py-14 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        {{-- Brand region --}}
        <div class="flex flex-col items-center">
            <a href="{{ route('home') }}" class="flex items-center gap-2" aria-label="{{ config('app.name', 'Foursouls') }} home">
                <span class="brand-mark" aria-hidden="true">FS</span>
                <span class="text-xl font-bold tracking-tight text-gray-900">{{ config('app.name', 'Foursouls') }}</span>
            </a>
            <h1 class="mt-6 text-center text-2xl font-bold tracking-tight text-gray-900">Create your account</h1>
            <p class="mt-2 text-center text-sm text-gray-600">
                Start learning in minutes. Already have an account?
                <a href="{{ route('login') }}" class="font-semibold text-blue-600 hover:text-blue-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">Sign in</a>
            </p>
        </div>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-2xl">
        <div class="auth-panel rounded-2xl px-5 py-8 sm:px-10">
            <x-flash-message/>

            <form method="POST" action="{{ route('register.store') }}" class="space-y-6" novalidate>
                @csrf

                {{-- Full name --}}
                <div>
                    <label for="full_name" class="block text-sm font-medium leading-6 text-gray-900">
                        Full Name <span class="text-red-500" aria-hidden="true">*</span>
                    </label>
                    <div class="mt-2">
                        <input id="full_name" name="full_name" type="text" autocomplete="name" required value="{{ old('full_name') }}"
                               aria-required="true" {{ $errors->has('full_name') ? 'aria-invalid="true" aria-describedby="full_name-error"' : '' }}
                               class="auth-field block w-full px-3 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6 {{ $errors->has('full_name') ? 'ring-red-300' : 'ring-gray-300' }}">
                    </div>
                    @error('full_name')
                        <p id="full_name-error" class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

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

                {{-- Mobile: country code + number --}}
                <fieldset>
                    <legend class="block text-sm font-medium leading-6 text-gray-900">
                        Mobile Number <span class="text-red-500" aria-hidden="true">*</span>
                    </legend>
                    <div class="mt-2 grid grid-cols-1 gap-3 sm:grid-cols-[minmax(0,11rem)_minmax(0,1fr)]">
                        <div>
                            <label for="mobile_country_code" class="sr-only">Country code</label>
                            <select id="mobile_country_code" name="mobile_country_code"
                                    {{ $errors->has('mobile_country_code') ? 'aria-invalid="true"' : '' }}
                                    class="auth-field block w-full bg-white px-3 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6 {{ $errors->has('mobile_country_code') ? 'ring-red-300' : 'ring-gray-300' }}">
                                @foreach (\App\Support\CountryCodes::dialingOptions() as $option)
                                    {{-- The request stores the dialing code (+91), not the ISO code (IN). --}}
                                    <option value="{{ $option['dial'] }}" data-country="{{ $option['code'] }}" {{ in_array(old('mobile_country_code', '+971'), [$option['dial'], $option['code']], true) ? 'selected' : '' }}>
                                        {{ $option['flag'] }} {{ $option['name'] }} ({{ $option['dial'] }})
                                    </option>
                                @endforeach
                            </select>
                            @error('mobile_country_code')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="mobile_number" class="sr-only">Mobile number</label>
                            <input id="mobile_number" name="mobile_number" type="tel" inputmode="numeric" autocomplete="tel-national" required value="{{ old('mobile_number') }}"
                                   placeholder="501234567" aria-required="true" {{ $errors->has('mobile_number') ? 'aria-invalid="true" aria-describedby="mobile_number-error"' : '' }}
                                   class="auth-field block w-full px-3 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6 {{ $errors->has('mobile_number') ? 'ring-red-300' : 'ring-gray-300' }}">
                            @error('mobile_number')
                                <p id="mobile_number-error" class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <p class="mt-2 text-xs text-gray-500">Enter your number without the country code. We use it for account security only.</p>
                </fieldset>

                {{-- Preferred language --}}
                <div>
                    <label for="preferred_language" class="block text-sm font-medium leading-6 text-gray-900">
                        Preferred Language <span class="text-red-500" aria-hidden="true">*</span>
                    </label>
                    <div class="mt-2">
                        <select id="preferred_language" name="preferred_language"
                                {{ $errors->has('preferred_language') ? 'aria-invalid="true"' : '' }}
                                class="auth-field block w-full bg-white px-3 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6 {{ $errors->has('preferred_language') ? 'ring-red-300' : 'ring-gray-300' }}">
                            @foreach (\App\Support\Locales::options() as $value => $label)
                                <option value="{{ $value }}" {{ old('preferred_language', 'en') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('preferred_language')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password + confirmation --}}
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label for="password" class="block text-sm font-medium leading-6 text-gray-900">
                            Password <span class="text-red-500" aria-hidden="true">*</span>
                        </label>
                        <div class="relative mt-2">
                            <input id="password" name="password" type="password" autocomplete="new-password" required
                                   aria-required="true" aria-describedby="password-hint" {{ $errors->has('password') ? 'aria-invalid="true" aria-describedby="password-error password-hint"' : '' }}
                                   class="auth-field block w-full px-3 py-2.5 pr-16 text-gray-900 shadow-sm ring-1 ring-inset placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6 {{ $errors->has('password') ? 'ring-red-300' : 'ring-gray-300' }}">
                            <button type="button" data-password-toggle="password" data-toggle-state="hidden"
                                    class="absolute inset-y-0 right-0 flex items-center px-3 text-xs font-semibold text-blue-600 hover:text-blue-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600"
                                    aria-label="Show password">Show</button>
                        </div>
                        <p id="password-hint" class="mt-2 text-xs text-gray-500">At least 8 characters, with letters and numbers.</p>
                        @error('password')
                            <p id="password-error" class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium leading-6 text-gray-900">
                            Confirm Password <span class="text-red-500" aria-hidden="true">*</span>
                        </label>
                        <div class="relative mt-2">
                            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required
                                   aria-required="true" {{ $errors->has('password_confirmation') ? 'aria-invalid="true" aria-describedby="password_confirmation-error"' : '' }}
                                   class="auth-field block w-full px-3 py-2.5 pr-16 text-gray-900 shadow-sm ring-1 ring-inset placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6 {{ $errors->has('password_confirmation') ? 'ring-red-300' : 'ring-gray-300' }}">
                            <button type="button" data-password-toggle="password_confirmation" data-toggle-state="hidden"
                                    class="absolute inset-y-0 right-0 flex items-center px-3 text-xs font-semibold text-blue-600 hover:text-blue-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600"
                                    aria-label="Show password confirmation">Show</button>
                        </div>
                        @error('password_confirmation')
                            <p id="password_confirmation-error" class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Consents --}}
                <div class="space-y-4 rounded-xl bg-gray-50 p-4 ring-1 ring-gray-200">
                    <div class="flex items-start gap-3">
                        <input id="terms_acceptance" name="terms_acceptance" type="checkbox" value="1" required
                               aria-required="true" {{ old('terms_acceptance') ? 'checked' : '' }} {{ $errors->has('terms_acceptance') ? 'aria-invalid="true" aria-describedby="terms_acceptance-error"' : '' }}
                               class="mt-1 h-4 w-4 shrink-0 rounded border-gray-300 text-blue-600 focus:ring-blue-600">
                        <label for="terms_acceptance" class="text-sm leading-6 text-gray-700">
                            I accept the
                            <a href="{{ route('legal.terms') }}" target="_blank" rel="noopener" class="font-semibold text-blue-600 hover:text-blue-700 underline underline-offset-2">Terms of Service</a>
                            (version {{ \App\Support\LegalDocuments::termsVersion() }}).
                            <span class="text-red-500" aria-hidden="true">*</span>
                        </label>
                    </div>
                    @error('terms_acceptance')
                        <p id="terms_acceptance-error" class="-mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror

                    <div class="flex items-start gap-3">
                        <input id="privacy_acceptance" name="privacy_acceptance" type="checkbox" value="1" required
                               aria-required="true" {{ old('privacy_acceptance') ? 'checked' : '' }} {{ $errors->has('privacy_acceptance') ? 'aria-invalid="true" aria-describedby="privacy_acceptance-error"' : '' }}
                               class="mt-1 h-4 w-4 shrink-0 rounded border-gray-300 text-blue-600 focus:ring-blue-600">
                        <label for="privacy_acceptance" class="text-sm leading-6 text-gray-700">
                            I accept the
                            <a href="{{ route('legal.privacy') }}" target="_blank" rel="noopener" class="font-semibold text-blue-600 hover:text-blue-700 underline underline-offset-2">Privacy Policy</a>
                            (version {{ \App\Support\LegalDocuments::privacyVersion() }}).
                            <span class="text-red-500" aria-hidden="true">*</span>
                        </label>
                    </div>
                    @error('privacy_acceptance')
                        <p id="privacy_acceptance-error" class="-mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror

                    {{-- Marketing consent is an explicit Yes/No choice, not an optional checkbox --}}
                    <fieldset>
                        <legend class="text-sm font-medium leading-6 text-gray-700">
                            Marketing emails <span class="text-red-500" aria-hidden="true">*</span>
                        </legend>
                        <p class="mt-1 text-xs text-gray-500">Please choose one option. You can change this anytime in Preferences.</p>
                        <div class="mt-2 flex flex-col gap-2 sm:flex-row sm:gap-6">
                            <div class="flex items-center gap-2">
                                <input id="marketing_consent_yes" name="marketing_consent" type="radio" value="1"
                                       required aria-required="true" {{ old('marketing_consent') === '1' ? 'checked' : '' }} {{ $errors->has('marketing_consent') ? 'aria-invalid="true"' : '' }}
                                       class="h-4 w-4 shrink-0 border-gray-300 text-blue-600 focus:ring-blue-600">
                                <label for="marketing_consent_yes" class="text-sm leading-6 text-gray-700">Yes, send me updates/offers</label>
                            </div>
                            <div class="flex items-center gap-2">
                                <input id="marketing_consent_no" name="marketing_consent" type="radio" value="0"
                                       required aria-required="true" {{ old('marketing_consent') === '0' ? 'checked' : '' }} {{ $errors->has('marketing_consent') ? 'aria-invalid="true"' : '' }}
                                       class="h-4 w-4 shrink-0 border-gray-300 text-blue-600 focus:ring-blue-600">
                                <label for="marketing_consent_no" class="text-sm leading-6 text-gray-700">No, do not send marketing messages</label>
                            </div>
                        </div>
                        @error('marketing_consent')
                            <p id="marketing_consent-error" class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </fieldset>
                </div>

                <div>
                    <button type="submit"
                            class="auth-submit flex w-full justify-center px-3 py-2.5 text-sm font-semibold text-white shadow-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 disabled:cursor-not-allowed disabled:opacity-60">
                        Create account
                    </button>
                </div>
            </form>
        </div>

        <p class="mt-6 text-center text-xs text-gray-500">
            By creating an account you agree that your data will be processed as described in our Privacy Policy.
        </p>
    </div>
</div>
@endsection

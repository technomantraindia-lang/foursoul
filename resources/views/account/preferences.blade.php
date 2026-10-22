@extends('layouts.app')

@section('title', __('Preferences'))

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Page heading --}}
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900 sm:text-3xl">{{ __('Preferences') }}</h1>
        <p class="mt-2 text-sm text-gray-600">{{ __('Choose how content and prices are displayed for your account.') }}</p>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Preferences form --}}
        <div class="lg:col-span-2">
            <form method="POST" action="{{ route('account.preferences.update') }}" class="bg-white shadow-sm ring-1 ring-gray-900/5 rounded-xl sm:rounded-2xl">
                @csrf
                @method('PUT')

                <div class="px-4 py-5 sm:p-6 space-y-6">
                    <div>
                        <h2 class="text-base font-semibold leading-7 text-gray-900">{{ __('Display Preferences') }}</h2>
                        <p class="mt-1 text-sm text-gray-500">{{ __('Your preferred currency overrides automatic region detection where allowed.') }}</p>
                    </div>

                    {{-- Preferred language --}}
                    <div>
                        <label for="preferred_language" class="block text-sm font-medium leading-6 text-gray-900">
                            {{ __('Preferred Language') }}
                        </label>
                        <div class="mt-2">
                            <select id="preferred_language" name="preferred_language"
                                class="block w-full rounded-lg border-0 px-3 py-2 text-gray-900 shadow-sm ring-1 ring-inset focus:ring-2 focus:ring-inset sm:text-sm sm:leading-6 {{ $errors->has('preferred_language') ? 'ring-red-300 focus:ring-red-500' : 'ring-gray-300 focus:ring-blue-600' }}">
                                @foreach (\App\Support\Locales::all() as $code => $label)
                                    <option value="{{ $code }}" {{ old('preferred_language', $user->preferred_language ?? '') === $code ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @error('preferred_language')
                            <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Preferred currency — only AED / USD / INR via the shared enum (§20–21) --}}
                    <fieldset>
                        <legend class="block text-sm font-medium leading-6 text-gray-900">{{ __('Preferred Currency') }}</legend>
                        <p class="mt-1 text-xs text-gray-500">{{ __('Prices are shown in this currency. No exchange-rate conversion is applied.') }}</p>

                        <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-3">
                            @foreach (\App\Support\Currency::cases() as $currency)
                                <label class="relative flex cursor-pointer items-start gap-3 rounded-lg border p-4 has-[:focus-visible]:outline-none has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-blue-500 has-[:focus-visible]:ring-offset-2 {{ old('preferred_currency', $user->preferred_currency?->value ?? '') === $currency->value ? 'border-blue-600 ring-1 ring-blue-600 bg-blue-50' : 'border-gray-300 hover:bg-gray-50' }}">
                                    <input type="radio" name="preferred_currency" value="{{ $currency->value }}"
                                        class="mt-0.5 h-4 w-4 border-gray-300 text-blue-600 focus:ring-blue-600"
                                        {{ old('preferred_currency', $user->preferred_currency?->value ?? '') === $currency->value ? 'checked' : '' }}>
                                    <span class="flex flex-col">
                                        <span class="block text-sm font-medium text-gray-900">{{ $currency->value }} — {{ $currency->symbol() }}</span>
                                        <span class="block text-xs text-gray-500">{{ $currency->label() }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        @error('preferred_currency')
                            <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
                        @enderror
                    </fieldset>

                    {{-- Timezone --}}
                    <div>
                        <label for="timezone" class="block text-sm font-medium leading-6 text-gray-900">
                            {{ __('Timezone') }}
                        </label>
                        <div class="mt-2">
                            <select id="timezone" name="timezone"
                                class="block w-full rounded-lg border-0 px-3 py-2 text-gray-900 shadow-sm ring-1 ring-inset focus:ring-2 focus:ring-inset sm:text-sm sm:leading-6 {{ $errors->has('timezone') ? 'ring-red-300 focus:ring-red-500' : 'ring-gray-300 focus:ring-blue-600' }}">
                                <option value="">{{ __('Select a timezone…') }}</option>
                                @foreach (\App\Support\Timezones::grouped() as $region => $zones)
                                    <optgroup label="{{ $region }}">
                                        @foreach ($zones as $identifier => $label)
                                            <option value="{{ $identifier }}" {{ old('timezone', $user->timezone ?? '') === $identifier ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>
                        @error('timezone')
                            <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Detected info (read-only transparency) --}}
                    <div class="rounded-lg bg-gray-50 p-4">
                        <h3 class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Detected Region (automatic)') }}</h3>
                        <dl class="mt-2 grid grid-cols-1 gap-x-6 gap-y-2 text-sm sm:grid-cols-2">
                            <div class="flex justify-between gap-3">
                                <dt class="text-gray-500">{{ __('Country') }}</dt>
                                <dd class="font-medium text-gray-900">{{ $user->detected_country_code ?? __('Unknown') }}</dd>
                            </div>
                            <div class="flex justify-between gap-3">
                                <dt class="text-gray-500">{{ __('Currency') }}</dt>
                                <dd class="font-medium text-gray-900">{{ $user->detected_currency?->value ?? __('Unknown') }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                {{-- Form actions --}}
                <div class="flex items-center justify-end gap-3 border-t border-gray-200 px-4 py-4 sm:px-6 rounded-b-xl sm:rounded-b-2xl bg-gray-50">
                    <a href="{{ route('dashboard') }}" class="rounded-lg px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500">
                        {{ __('Cancel') }}
                    </a>
                    <button type="submit"
                        class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 disabled:opacity-50 disabled:pointer-events-none">
                        {{ __('Save Preferences') }}
                    </button>
                </div>
            </form>
        </div>

        {{-- Info sidebar --}}
        <aside class="space-y-6" aria-label="{{ __('About Preferences') }}">
            <div class="bg-white shadow-sm ring-1 ring-gray-900/5 rounded-xl sm:rounded-2xl p-5 sm:p-6">
                <h2 class="text-sm font-semibold text-gray-900">{{ __('How Currency Works') }}</h2>
                <ul class="mt-4 space-y-3 text-sm text-gray-600 list-disc list-inside">
                    <li>{{ __('AED, USD and INR are supported.') }}</li>
                    <li>{{ __('Your manual choice always wins when set.') }}</li>
                    <li>{{ __('Otherwise we use the currency detected from your region.') }}</li>
                    <li>{{ __('If nothing matches, USD is used.') }}</li>
                </ul>
            </div>
        </aside>
    </div>
</div>
@endsection
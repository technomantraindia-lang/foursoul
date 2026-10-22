@extends('layouts.app')

@section('title', __('Profile'))

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Page heading --}}
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900 sm:text-3xl">{{ __('Profile') }}</h1>
        <p class="mt-2 text-sm text-gray-600">{{ __('Manage your personal information and account details.') }}</p>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Main form column --}}
        <div class="lg:col-span-2 space-y-6">
            <form method="POST" action="{{ route('account.profile.update') }}" enctype="multipart/form-data" class="bg-white shadow-sm ring-1 ring-gray-900/5 rounded-xl sm:rounded-2xl" x-data="{ showPasswordModal: false }">
                @csrf
                @method('PUT')

                <div class="px-4 py-5 sm:p-6 space-y-6">
                    {{-- Avatar (§18) --}}
                    <div>
                        <h2 class="text-base font-semibold leading-7 text-gray-900">{{ __('Profile Photo') }}</h2>
                        <p class="mt-1 text-sm text-gray-500">{{ __('JPG, PNG or WebP up to 2 MB.') }}</p>

                        <div class="mt-4 flex items-center gap-5">
                            <div class="relative">
                                @if ($user->avatar_path)
                                    <img src="{{ Storage::url($user->avatar_path) }}" alt="{{ __('Avatar of :name', ['name' => $user->full_name]) }}" class="h-20 w-20 rounded-full object-cover ring-2 ring-gray-200">
                                @else
                                    <span class="inline-flex h-20 w-20 items-center justify-center rounded-full bg-blue-100 text-blue-700 text-xl font-semibold uppercase ring-2 ring-gray-200">
                                        {{ mb_substr($user->full_name, 0, 1) }}
                                    </span>
                                @endif
                            </div>

                            <div class="flex flex-col gap-2">
                                <label for="avatar" class="inline-flex cursor-pointer items-center justify-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus-within:outline-none focus-within:ring-2 focus-within:ring-blue-500 focus-within:ring-offset-2">
                                    {{ __('Choose photo') }}
                                    <input type="file" id="avatar" name="avatar" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="sr-only"
                                        onchange="document.getElementById('avatar-file-name').textContent = this.files.length ? this.files[0].name : '';">
                                </label>
                                <span id="avatar-file-name" class="text-xs text-gray-500"></span>
                                @error('avatar')
                                    <p class="text-sm text-red-600" role="alert">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <hr class="border-gray-200">

                    {{-- Personal information --}}
                    <div>
                        <h2 class="text-base font-semibold leading-7 text-gray-900">{{ __('Personal Information') }}</h2>

                        <div class="mt-4 grid grid-cols-1 gap-x-6 gap-y-5 sm:grid-cols-6">
                            {{-- Full name --}}
                            <div class="sm:col-span-6">
                                <label for="full_name" class="block text-sm font-medium leading-6 text-gray-900">
                                    {{ __('Full Name') }} <span class="text-red-500">*</span>
                                </label>
                                <div class="mt-2">
                                    <input type="text" id="full_name" name="full_name" value="{{ old('full_name', $user->full_name) }}" required autocomplete="name"
                                        class="block w-full rounded-lg border-0 px-3 py-2 text-gray-900 shadow-sm ring-1 ring-inset placeholder:text-gray-400 focus:ring-2 focus:ring-inset sm:text-sm sm:leading-6 {{ $errors->has('full_name') ? 'ring-red-300 focus:ring-red-500' : 'ring-gray-300 focus:ring-blue-600' }}">
                                </div>
                                @error('full_name')
                                    <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Email — read-only until secure email-change flow exists (§17) --}}
                            <div class="sm:col-span-6">
                                <label for="email" class="block text-sm font-medium leading-6 text-gray-900">
                                    {{ __('Email Address') }} <span class="text-red-500">*</span>
                                </label>
                                <div class="mt-2 relative">
                                    <input type="email" id="email" value="{{ $user->email }}" readonly disabled autocomplete="email"
                                        aria-describedby="email-hint"
                                        class="block w-full rounded-lg border-0 px-3 py-2 text-gray-500 bg-gray-50 shadow-sm ring-1 ring-inset ring-gray-300 sm:text-sm sm:leading-6 cursor-not-allowed">
                                    <p id="email-hint" class="mt-2 text-xs text-gray-500">{{ __('Email address changes require identity verification and are not available yet.') }}</p>
                                </div>
                            </div>

                            {{-- Mobile country code + number --}}
                            <div class="sm:col-span-2">
                                <label for="mobile_country_code" class="block text-sm font-medium leading-6 text-gray-900">
                                    {{ __('Country Code') }} <span class="text-red-500">*</span>
                                </label>
                                <div class="mt-2">
                                    <select id="mobile_country_code" name="mobile_country_code" required
                                        class="block w-full rounded-lg border-0 px-3 py-2 text-gray-900 shadow-sm ring-1 ring-inset focus:ring-2 focus:ring-inset sm:text-sm sm:leading-6 {{ $errors->has('mobile_country_code') ? 'ring-red-300 focus:ring-red-500' : 'ring-gray-300 focus:ring-blue-600' }}">
                                        @foreach (\App\Support\CountryCodes::dialingOptions() as $option)
                                            <option value="{{ $option['dial'] }}"
                                                {{ old('mobile_country_code', $user->mobile_country_code ?? '+971') === $option['dial'] ? 'selected' : '' }}>
                                                {{ $option['flag'] }} {{ $option['dial'] }} {{ $option['name'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('mobile_country_code')
                                    <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="sm:col-span-4">
                                <label for="mobile_number" class="block text-sm font-medium leading-6 text-gray-900">
                                    {{ __('Mobile Number') }} <span class="text-red-500">*</span>
                                </label>
                                <div class="mt-2">
                                    <input type="tel" id="mobile_number" name="mobile_number" value="{{ old('mobile_number', $user->mobile_number) }}" required autocomplete="tel-national" inputmode="numeric"
                                        placeholder="{{ __('e.g. 501234567') }}"
                                        class="block w-full rounded-lg border-0 px-3 py-2 text-gray-900 shadow-sm ring-1 ring-inset placeholder:text-gray-400 focus:ring-2 focus:ring-inset sm:text-sm sm:leading-6 {{ $errors->has('mobile_number') ? 'ring-red-300 focus:ring-red-500' : 'ring-gray-300 focus:ring-blue-600' }}">
                                </div>
                                @error('mobile_number')
                                    <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Preferred language --}}
                            <div class="sm:col-span-3">
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

                            {{-- Timezone --}}
                            <div class="sm:col-span-3">
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
                        </div>
                    </div>
                </div>

                {{-- Form actions --}}
                <div class="flex items-center justify-end gap-3 border-t border-gray-200 px-4 py-4 sm:px-6 rounded-b-xl sm:rounded-b-2xl bg-gray-50">
                    <a href="{{ route('dashboard') }}" class="rounded-lg px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500">
                        {{ __('Cancel') }}
                    </a>
                    <button type="submit"
                        class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 disabled:opacity-50 disabled:pointer-events-none">
                        {{ __('Save Changes') }}
                    </button>
                </div>
            </form>
        </div>

        {{-- Side summary column --}}
        <aside class="space-y-6" aria-label="{{ __('Account Summary') }}">
            <div class="bg-white shadow-sm ring-1 ring-gray-900/5 rounded-xl sm:rounded-2xl p-5 sm:p-6">
                <h2 class="text-sm font-semibold text-gray-900">{{ __('Account Summary') }}</h2>
                <dl class="mt-4 space-y-4 text-sm">
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-gray-500">{{ __('Email Verification') }}</dt>
                        <dd>
                            @if ($user->hasVerifiedEmail())
                                <span class="inline-flex items-center gap-1 rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">✓ {{ __('Verified') }}</span>
                            @else
                                <a href="{{ route('verification.notice') }}" class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2 py-0.5 text-xs font-medium text-amber-700 ring-1 ring-inset ring-amber-600/20 hover:bg-amber-100">{{ __('Pending') }}</a>
                            @endif
                        </dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-gray-500">{{ __('Mobile Verification') }}</dt>
                        <dd>
                            @if ($user->mobile_verified_at)
                                <span class="inline-flex items-center gap-1 rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">✓ {{ __('Verified') }}</span>
                            @else
                                <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/20">{{ __('Not verified yet') }}</span>
                            @endif
                        </dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-gray-500">{{ __('Account Status') }}</dt>
                        <dd>
                            <span class="inline-flex items-center gap-1 rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">
                                {{ $user->status->label() }}
                            </span>
                        </dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-gray-500">{{ __('Member Since') }}</dt>
                        <dd class="font-medium text-gray-900">{{ $user->created_at?->translatedFormat('M j, Y') ?? '—' }}</dd>
                    </div>
                </dl>
            </div>
        </aside>
    </div>
</div>
@endsection
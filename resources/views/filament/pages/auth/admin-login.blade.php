{{-- Prompt 2B (6): Administration Portal login view --}}
{{-- Professional, accessible, responsive login screen for admins only --}}
{{-- No customer register/login/social options --}}

<x-filament-panels::page.simple>
    <div class="min-h-screen flex items-center justify-center bg-gray-50 dark:bg-gray-900 px-4 py-12 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            {{-- Branding area --}}
            <div class="text-center">
                @php
                    $siteName = config('settings.defaults.site_name', 'Foursouls');
                    $panelName = config('settings.defaults.admin_panel_name', 'Administration Portal');
                @endphp

                <div class="mx-auto h-16 w-16 rounded-xl bg-primary-600 flex items-center justify-center mb-6" aria-hidden="true">
                    <svg class="h-10 w-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>

                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $panelName }}</h1>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Sign in to manage') }} {{ $siteName }}
                </p>
            </div>

            {{-- Login form --}}
            <form class="mt-8 space-y-6" method="POST" action="{{ filament()->getLoginUrl() }}">
                @csrf

                {{-- Email field --}}
                <div>
                    <label for="email" class="sr-only">{{ __('Admin Email') }}</label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        autocomplete="username"
                        required
                        class="appearance-none relative block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 placeholder-gray-500 dark:placeholder-gray-400 text-gray-900 dark:text-white rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500 focus:z-10 sm:text-sm dark:bg-gray-800"
                        placeholder="{{ __('Admin Email') }}"
                        value="{{ old('email') }}"
                        aria-describedby="email-error"
                    >
                    @error('email')
                        <p id="email-error" class="mt-1 text-sm text-red-600 dark:text-red-400" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password field --}}
                <div>
                    <label for="password" class="sr-only">{{ __('Password') }}</label>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        autocomplete="current-password"
                        required
                        class="appearance-none relative block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 placeholder-gray-500 dark:placeholder-gray-400 text-gray-900 dark:text-white rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500 focus:z-10 sm:text-sm dark:bg-gray-800"
                        placeholder="{{ __('Password') }}"
                        aria-describedby="password-error"
                    >
                    @error('password')
                        <p id="password-error" class="mt-1 text-sm text-red-600 dark:text-red-400" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Remember me --}}
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input
                            id="remember"
                            name="remember"
                            type="checkbox"
                            class="h-4 w-4 text-primary-600 border-gray-300 dark:border-gray-600 rounded focus:ring-primary-500 focus:ring-2 dark:bg-gray-700"
                        >
                        <label for="remember" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">
                            {{ __('Remember me') }}
                        </label>
                    </div>

                    {{-- Forgot password link (architecture placeholder) --}}
                    <div class="text-sm">
                        <a
                            href="{{ route('filament.admin.auth.password-reset.request') }}"
                            class="font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300"
                        >
                            {{ __('Forgot password?') }}
                        </a>
                    </div>
                </div>

                {{-- Submit button --}}
                <div>
                    <button
                        type="submit"
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:focus:ring-offset-gray-800 transition-colors duration-200"
                    >
                        {{ __('Sign in') }}
                    </button>
                </div>
            </form>

            {{-- Footer note --}}
            <p class="text-center text-xs text-gray-500 dark:text-gray-400">
                {{ __('Administration access only. Customer accounts cannot sign in here.') }}
            </p>
        </div>
    </div>
</x-filament-panels::page.simple>

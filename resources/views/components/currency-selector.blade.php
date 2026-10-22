@php
    use App\Support\Currency;

    // Resolve the effective display currency: user preference first,
    // then session/detected value, then USD fallback (§21).
    $currentCurrency = Currency::tryFromCode(
        auth()->user()?->preferred_currency ?? session('currency')
    ) ?? Currency::USD;
@endphp
<a href="{{ auth()->check() ? route('account.preferences.edit') : route('register') }}"
   class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-2.5 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600"
   title="Currency: {{ $currentCurrency->label() }}{{ auth()->check() ? ' — change in Preferences' : '' }}"
   aria-label="Current currency {{ $currentCurrency->value }}{{ auth()->check() ? ', change in Preferences' : ', register to set your currency' }}">
    <svg class="w-4 h-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.798 7.45c.64-1.24 1.63-1.95 2.702-1.95 1.073 0 2.063.71 2.702 1.95a.75.75 0 01-1.336.68C12.566 7.617 12.034 7.25 11.5 7.25c-.534 0-1.066.367-1.366.88a.75.75 0 11-1.336-.68zM9 9.75a.75.75 0 00-1.5 0v.5a.75.75 0 001.5 0v-.5zm3.5 0a.75.75 0 00-1.5 0v.5a.75.75 0 001.5 0v-.5zM8.25 13a.75.75 0 01.75-.75h2a.75.75 0 010 1.5H9a.75.75 0 01-.75-.75z" clip-rule="evenodd"/>
    </svg>
    <span>{{ $currentCurrency->value }}</span>
</a>
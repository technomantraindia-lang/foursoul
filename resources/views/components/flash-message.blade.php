@php
    // §25 — consistent feedback keys used across controllers:
    // status (success), error, and info. Renders nothing when empty.
    $flashTypes = [
        'status' => ['class' => 'border-green-200 bg-green-50 text-green-800', 'icon' => 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'role' => 'status'],
        'error' => ['class' => 'border-red-200 bg-red-50 text-red-800', 'icon' => 'M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z', 'role' => 'alert'],
        'info' => ['class' => 'border-blue-200 bg-blue-50 text-blue-800', 'icon' => 'M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-4.25-1.5h.008v.008h-.008V10.5z', 'role' => 'status'],
    ];
@endphp
<div class="space-y-3" aria-live="polite">
    @foreach ($flashTypes as $key => $meta)
        @if (session()->has($key))
            <div class="flex items-start gap-3 rounded-xl border px-4 py-3 {{ $meta['class'] }}" role="{{ $meta['role'] }}">
                <svg class="mt-0.5 h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $meta['icon'] }}"/>
                </svg>
                <p class="text-sm font-medium">{{ session($key) }}</p>
            </div>
        @endif
    @endforeach
</div>
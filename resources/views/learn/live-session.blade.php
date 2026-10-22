@extends('layouts.app')

@section('title', $session->title)

@section('content')
<div class="max-w-3xl mx-auto px-4 py-8">
    <a href="{{ route('learn.live-sessions.index') }}" class="text-sm text-blue-600 hover:underline">&larr; All live sessions</a>

    <div class="mt-4 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $session->title }}</h1>
                <p class="mt-1 text-sm text-gray-500">{{ $session->course?->title }} · Hosted by {{ $session->instructor?->name }}</p>
            </div>
            <span class="rounded-full px-3 py-1 text-xs font-semibold
                {{ in_array($session->status->value, ['live']) ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
                {{ ucfirst($session->status->value) }}
            </span>
        </div>

        @if ($session->description)
            <p class="mt-4 text-gray-700 whitespace-pre-line">{{ $session->description }}</p>
        @endif

        <dl class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
            <div>
                <dt class="text-gray-500">Starts</dt>
                <dd class="font-medium text-gray-900">{{ $session->scheduled_start_at?->timezone($timezone)->format('D, M j, Y g:i A') }} ({{ $timezone }})</dd>
            </div>
            <div>
                <dt class="text-gray-500">Ends</dt>
                <dd class="font-medium text-gray-900">{{ $session->scheduled_end_at?->timezone($timezone)->format('D, M j, Y g:i A') }} ({{ $timezone }})</dd>
            </div>
            <div>
                <dt class="text-gray-500">Provider</dt>
                <dd class="font-medium text-gray-900">{{ $session->provider->getLabel() }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Session type</dt>
                <dd class="font-medium text-gray-900">{{ $session->session_type->getLabel() }}</dd>
            </div>
        </dl>

        <div class="mt-6" data-session-countdown data-start="{{ $session->scheduled_start_at?->utc()->toIso8601String() }}" data-end="{{ $session->scheduled_end_at?->utc()->toIso8601String() }}">
            <p class="text-sm font-semibold text-gray-700" data-countdown-state>Loading session state...</p>
            <p class="mt-1 text-sm text-gray-500" data-countdown-value></p>
        </div>

        @if ($youtubeEmbedUrl)
            <div class="mt-6 aspect-video overflow-hidden rounded-xl bg-black">
                <iframe class="h-full w-full" src="{{ $youtubeEmbedUrl }}" title="{{ $session->title }}" allow="autoplay; encrypted-media; picture-in-picture" allowfullscreen></iframe>
            </div>
        @endif

        {{-- Join / waiting room --}}
        <div class="mt-8">
            @if ($joinState['can_join'])
                @if ($joinState['waiting_room'])
                    <form method="POST" action="{{ route('learn.live-sessions.join', $session) }}">
                        @csrf
                        <button type="submit" class="w-full rounded-lg bg-orange-600 px-5 py-3 font-semibold text-white hover:bg-orange-700 transition">
                            Enter Waiting Room
                        </button>
                    </form>
                    <p class="mt-2 text-xs text-gray-500">The host will admit you when the session begins.</p>
                @else
                    <form method="POST" action="{{ route('learn.live-sessions.join', $session) }}">
                        @csrf
                        <button type="submit" class="w-full rounded-lg bg-blue-600 px-5 py-3 font-semibold text-white hover:bg-blue-700 transition">
                            Join Session
                        </button>
                    </form>
                @endif
            @else
                <div class="rounded-lg bg-gray-50 border border-gray-200 p-4 text-sm text-gray-600">
                    @if ($joinState['locked_reason'] === 'outside_join_window')
                        Joining opens {{ \Carbon\Carbon::parse($joinState['starts_at'])->timezone($timezone)->subMinutes(15)->format('g:i A') }}.
                    @elseif ($joinState['locked_reason'])
                        This session is not available for you to join right now.
                    @endif
                </div>
            @endif
        </div>

        {{-- Replay --}}
        @if ($replayUrl)
            <div class="mt-6 rounded-xl bg-indigo-50 border border-indigo-200 p-5">
                <h2 class="font-semibold text-indigo-900">Session Replay</h2>
                <a href="{{ $replayUrl }}" target="_blank" rel="noopener"
                   class="mt-2 inline-block rounded-lg bg-indigo-600 px-4 py-2 font-medium text-white hover:bg-indigo-700 transition">
                    Watch Replay
                </a>
            </div>
        @elseif (in_array($session->status->value, ['completed'], true))
            <p class="mt-6 text-sm text-gray-500">Replay is not available yet.</p>
        @endif

        {{-- Attendance summary --}}
        @if ($attendance)
            <div class="mt-6 rounded-xl border border-gray-200 p-4 text-sm text-gray-600">
                Attendance recorded:
                joined at {{ optional($attendance->joined_at)?->timezone($timezone)->format('g:i A') }}
                @if ($attendance->left_at)
                    · left at {{ $attendance->left_at->timezone($timezone)->format('g:i A') }}
                    · duration {{ $attendance->duration_minutes }} min
                @endif
            </div>
        @endif
    </div>
</div>
<script>
(() => {
    const root = document.querySelector('[data-session-countdown]');
    if (!root) return;
    const state = root.querySelector('[data-countdown-state]');
    const value = root.querySelector('[data-countdown-value]');
    const start = Date.parse(root.dataset.start);
    const end = Date.parse(root.dataset.end);
    const tick = () => {
        const now = Date.now();
        if (now < start) { state.textContent = 'UPCOMING'; value.textContent = 'Starts in ' + format(start - now); }
        else if (now <= end) { state.textContent = 'LIVE'; value.textContent = 'Session is in progress'; }
        else { state.textContent = 'ENDED'; value.textContent = 'This session has ended'; }
    };
    const format = ms => { const s = Math.max(0, Math.floor(ms / 1000)); const h = Math.floor(s / 3600); const m = Math.floor((s % 3600) / 60); return h + 'h ' + m + 'm ' + (s % 60) + 's'; };
    tick(); setInterval(tick, 1000);
})();
</script>
@endsection

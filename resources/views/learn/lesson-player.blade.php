@extends('layouts.app')

@section('title', $lesson->title . ' — ' . $course->title)

@php
    use App\Enums\LessonType;
    use App\Enums\LessonProgressStatus;
@endphp

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
    <a href="{{ route('learn.course', $course) }}" class="text-sm text-blue-700 hover:underline">← {{ $course->title }}</a>

    <p class="fs-kicker mt-6">{{ $course->title }}</p>
    <h1 class="mt-2 text-3xl font-bold tracking-tight text-gray-900">{{ $lesson->title }}</h1>

    @if ($lesson->type === LessonType::Video && $lesson->youtube_video_id)
        <div class="mt-6 aspect-video w-full overflow-hidden rounded-2xl bg-black shadow-xl">
            <iframe id="yt-player" class="w-full h-full" src="https://www.youtube.com/embed/{{ $lesson->youtube_video_id }}?enablejsapi=1&start={{ (int) $lessonProgress->last_position_seconds }}"
                    title="{{ $lesson->title }}" frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen></iframe>
        </div>
    @else
        <div class="fs-surface mt-6 p-6 prose max-w-none">
            {!! $lesson->content !!}
        </div>
    @endif

    <div class="mt-6 flex items-center justify-between gap-4">
        <div>
            @if ($previous)
                <a href="{{ route('learn.lesson', [$course, $previous]) }}"
                   class="inline-block rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    ← Previous
                </a>
            @endif
        </div>

        <div class="flex items-center gap-3">
            @if ($lessonProgress->status !== LessonProgressStatus::Completed)
                <button id="mark-complete" type="button"
                        data-url="{{ route('learn.lesson.complete', [$course, $lesson]) }}"
                        class="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">
                    Mark complete
                </button>
            @else
                <span class="text-sm font-medium text-green-700">✓ Completed</span>
            @endif

            @if ($next)
                <a href="{{ route('learn.lesson', [$course, $next]) }}"
                   class="inline-block rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                    Next →
                </a>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<meta name="csrf-token" content="{{ csrf_token() }}">
<script>
(function () {
    var lessonId = '{{ $lesson->uuid }}';
    var progressUrl = '{{ route('learn.lesson.progress', [$course, $lesson]) }}';
    var csrf = document.querySelector('meta[name="csrf-token"]').content;
    var lastSent = 0;

    function sendProgress(position, duration) {
        return fetch(progressUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: JSON.stringify({ position_seconds: Math.max(0, Math.floor(position)), duration_seconds: duration ? Math.floor(duration) : null }),
            keepalive: true,
        }).catch(function () {});
    }

    // YouTube IFrame API progress tracking
    var tag = document.createElement('script');
    tag.src = 'https://www.youtube.com/iframe_api';
    document.head.appendChild(tag);

    window.onYouTubeIframeAPIReady = function () {
        new YT.Player('yt-player', {
            events: {
                onReady: function (e) {
                    setInterval(function () {
                        var now = e.target.getCurrentTime() || 0;
                        if (now - lastSent >= 10) { // throttle to ~every 10s
                            lastSent = now;
                            sendProgress(now, e.target.getDuration());
                        }
                    }, 5000);
                },
                onStateChange: function (e) {
                    if (e.data === YT.PlayerState.ENDED) {
                        sendProgress(e.target.getCurrentTime(), e.target.getDuration()).then(function () {
                            var btn = document.getElementById('mark-complete');
                            if (btn) btn.click();
                        });
                    }
                },
            },
        });
    };

    var btn = document.getElementById('mark-complete');
    if (btn) {
        btn.addEventListener('click', function () {
            fetch(btn.dataset.url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            }).then(function () { window.location.reload(); }).catch(function () {});
        });
    }
})();
</script>
@endpush
@endsection

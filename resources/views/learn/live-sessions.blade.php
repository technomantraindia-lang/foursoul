@extends('layouts.app')

@section('title', 'Live Sessions')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Live Sessions</h1>
        <span class="text-sm text-gray-500">Times shown in {{ $timezone }}</span>
    </div>

    @if (session('flash.info'))
        <div class="mb-4 rounded-lg bg-blue-50 border border-blue-200 px-4 py-3 text-blue-800">{{ session('flash.info') }}</div>
    @endif

    {{-- Upcoming sessions grouped by day --}}
    @forelse ($days as $day)
        <h2 class="mt-8 mb-3 text-lg font-semibold text-gray-800">
            {{ \Carbon\Carbon::parse($day['date'])->timezone($timezone)->translatedFormat('l, F j, Y') }}
        </h2>

        <div class="space-y-3">
            @foreach ($day['sessions'] as $session)
                @php
                    $start = $session->scheduled_start_at->copy()->timezone($timezone);
                    $end = $session->scheduled_end_at->copy()->timezone($timezone);
                @endphp
                <a href="{{ route('learn.live-sessions.show', $session) }}"
                   class="block rounded-xl border border-gray-200 bg-white p-4 shadow-sm hover:border-blue-300 hover:shadow transition">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <p class="font-semibold text-gray-900">{{ $session->title }}</p>
                            <p class="text-sm text-gray-500">
                                {{ $session->course?->title }} · {{ $session->instructor?->name }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium text-gray-700">{{ $start->format('g:i A') }} – {{ $end->format('g:i A') }}</p>
                            <span class="inline-block mt-1 rounded-full px-2 py-0.5 text-xs font-medium
                                {{ $session->status->value === 'live' ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
                                {{ ucfirst($session->status->value) }}
                            </span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @empty
        <div class="rounded-xl border border-dashed border-gray-300 p-10 text-center text-gray-500">
            No upcoming live sessions for your enrolled courses.
        </div>
    @endforelse

    {{-- Past sessions --}}
    @if ($pastSessions->isNotEmpty())
        <h2 class="mt-12 mb-3 text-lg font-semibold text-gray-800">Past Sessions</h2>
        <div class="space-y-3">
            @foreach ($pastSessions as $session)
                <a href="{{ route('learn.live-sessions.show', $session) }}"
                   class="flex items-center justify-between rounded-xl border border-gray-200 bg-white p-4 shadow-sm hover:border-blue-300 transition">
                    <div>
                        <p class="font-semibold text-gray-900">{{ $session->title }}</p>
                        <p class="text-sm text-gray-500">{{ $session->course?->title }}</p>
                    </div>
                    <span class="text-sm text-gray-400">{{ $session->scheduled_start_at->copy()->timezone($timezone)->format('M j, Y') }}</span>
                </a>
            @endforeach
        </div>
    @endif
</div>
@endsection
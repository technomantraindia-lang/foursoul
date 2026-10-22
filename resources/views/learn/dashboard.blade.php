@extends('layouts.app')

@section('title', 'My Learning')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
    <p class="fs-kicker">Your learning space</p>
    <h1 class="mt-2 text-3xl font-bold tracking-tight text-gray-900 mb-1">My Learning</h1>
    <p class="text-sm text-gray-600 mb-6">Pick up where you left off and keep your momentum going.</p>

    @if ($continueEnrollment && $continueLesson)
        <a href="{{ route('learn.lesson', [$continueEnrollment->course, $continueLesson]) }}"
           class="fs-surface block mb-8 border-orange-200 bg-orange-50 p-5 hover:border-orange-300 transition">
            <span class="text-xs font-semibold uppercase tracking-wide text-blue-700">Continue learning</span>
            <span class="mt-1 block text-lg font-semibold text-blue-900">{{ $continueEnrollment->course->title }}</span>
            <span class="mt-0.5 block text-sm text-blue-800">{{ $continueLesson->title }}</span>
        </a>
    @endif

    @if ($enrollments->isEmpty())
        <div class="rounded-xl border border-dashed border-gray-300 p-10 text-center text-gray-500">
            You have no course access yet. Enroll in a plan to start learning.
        </div>
    @else
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($enrollments as $item)
                @php
                    $course = $item['course'];
                    $enrollment = $item['enrollment'];
                    $progress = $item['progress'];
                    $expired = $item['access_expired'];
                @endphp
                <div class="fs-surface {{ $expired ? 'border-red-200 bg-red-50' : '' }} p-5 flex flex-col">
                    <div class="flex items-center justify-between mb-2">
                        <h2 class="font-semibold text-gray-900">{{ $course->title }}</h2>
                        <span class="text-xs px-2 py-0.5 rounded-full font-medium
                            {{ $expired ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
                            {{ $expired ? 'Expired' : 'Active' }}
                        </span>
                    </div>
                    <div class="mb-3 h-2 rounded-full bg-gray-200 overflow-hidden" role="progressbar"
                         aria-valuenow="{{ (int) $progress?->progress_percentage }}" aria-valuemin="0" aria-valuemax="100">
                        <div class="h-full bg-blue-600" style="width: {{ (int) $progress?->progress_percentage }}%"></div>
                    </div>
                    <p class="text-xs text-gray-500 mb-4">
                        {{ (int) ($progress?->completed_lessons ?? 0) }} / {{ (int) ($progress?->total_lessons ?? 0) }} lessons completed
                        · {{ (int) round($progress?->progress_percentage ?? 0) }}%
                    </p>
                    <div class="mt-auto">
                        @if ($expired)
                            <span class="inline-block text-sm text-red-700">Access expired — renew to continue</span>
                        @else
                            <a href="{{ route('learn.course', $course) }}"
                               class="inline-block rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                                {{ $progress?->status === \App\Enums\CourseProgressStatus::Completed ? 'Review course' : 'Continue' }}
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection

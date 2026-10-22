@extends('layouts.app')

@section('title', $course->title)

@php
    use App\Enums\CourseProgressStatus;
@endphp

@section('content')
<div class="max-w-5xl mx-auto px-4 py-8">
    <div class="mb-6">
        <p class="fs-kicker">Course overview</p>
        <h1 class="mt-2 text-3xl font-bold tracking-tight text-gray-900">{{ $course->title }}</h1>
        @if ($course->description)
            <p class="mt-1 text-sm text-gray-600">{{ $course->description }}</p>
        @endif
        <div class="mt-3 h-2 max-w-md rounded-full bg-gray-200 overflow-hidden" role="progressbar"
             aria-valuenow="{{ (int) round($courseProgress->progress_percentage) }}" aria-valuemin="0" aria-valuemax="100">
            <div class="h-full bg-blue-600" style="width: {{ (int) round($courseProgress->progress_percentage) }}%"></div>
        </div>
        <p class="mt-1 text-xs text-gray-500">
            {{ (int) $courseProgress->completed_lessons }} / {{ (int) $courseProgress->total_lessons }} lessons
            · {{ (int) round($courseProgress->progress_percentage) }}%
            @if ($courseProgress->status === CourseProgressStatus::Completed)
                · <span class="font-medium text-green-700">Completed 🎉</span>
            @endif
        </p>
    </div>

    @if (! $hasAccess)
        <div class="rounded-xl border border-red-200 bg-red-50 p-5 text-sm text-red-700 mb-6">
            Your access to this course has expired or is unavailable. Renew your subscription to continue learning.
            Completed progress is preserved and will resume after renewal.
        </div>
    @endif

    <div class="space-y-8">
        @foreach ($sections as $item)
            <section>
                <h2 class="text-lg font-semibold text-gray-800 mb-3">{{ $item['section']->title }}</h2>
                <ul class="fs-surface divide-y divide-gray-100">
                    @foreach ($item['lessons'] as $entry)
                        @php
                            $lesson = $entry['lesson'];
                            $allowed = $entry['allowed'];
                            $completed = $entry['status'] === 'completed';
                        @endphp
                        <li class="flex items-center gap-3 px-4 py-3">
                            <span class="flex-shrink-0 w-6 text-center text-sm
                                {{ $completed ? 'text-green-600' : ($allowed ? 'text-blue-600' : 'text-gray-400') }}">
                                @if ($completed) ✓
                                @elseif (! $allowed) 🔒
                                @else ▶
                                @endif
                            </span>
                            <span class="flex-1 min-w-0">
                                @if ($allowed && $hasAccess)
                                    <a href="{{ route('learn.lesson', [$course, $lesson]) }}"
                                       class="block truncate font-medium text-gray-900 hover:text-blue-700">
                                        {{ $lesson->title }}
                                    </a>
                                @else
                                    <span class="block truncate font-medium text-gray-500">{{ $lesson->title }}</span>
                                    @if (! $allowed && $entry['locked_reason'])
                                        <span class="block text-xs text-gray-400">{{ str_replace('_', ' ', $entry['locked_reason']) }}</span>
                                    @endif
                                @endif
                            </span>
                            @if ((float) $entry['percentage'] > 0 && ! $completed)
                                <span class="text-xs text-gray-400">{{ (int) $entry['percentage'] }}%</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </section>
        @endforeach
    </div>

    {{-- Assessments are surfaced in the customer course workspace. --}}
    @if ($assignments->isNotEmpty() || $quizzes->isNotEmpty())
        <section class="mt-10" aria-labelledby="assessments-heading">
            <div class="flex items-end justify-between gap-3 mb-3">
                <div>
                    <p class="fs-kicker">Check your progress</p>
                    <h2 id="assessments-heading" class="mt-1 text-lg font-semibold text-gray-800">Assessments</h2>
                </div>
                <span class="text-xs text-gray-500">Complete these to finish your course</span>
            </div>
            <div class="space-y-3">
                @foreach ($assignments as $assignment)
                    <a href="{{ route('learn.assignment.show', [$course, $assignment]) }}" class="flex items-center gap-4 rounded-xl border border-gray-200 bg-white p-4 shadow-sm hover:border-orange-300 hover:shadow transition">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-orange-100 text-orange-700" aria-hidden="true">✓</span>
                        <span class="min-w-0 flex-1">
                            <span class="block font-semibold text-gray-900">{{ $assignment->title }}</span>
                            <span class="mt-1 block text-xs text-gray-500">Assignment · {{ $assignment->max_marks }} marks{{ $assignment->due_at ? ' · Due '.$assignment->due_at->format('d M Y') : '' }}</span>
                        </span>
                        <span class="text-sm font-medium text-orange-700">Open →</span>
                    </a>
                @endforeach
                @foreach ($quizzes as $quiz)
                    <a href="{{ route('learn.quiz.show', [$course, $quiz]) }}" class="flex items-center gap-4 rounded-xl border border-gray-200 bg-white p-4 shadow-sm hover:border-blue-300 hover:shadow transition">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-blue-100 text-blue-700" aria-hidden="true">?</span>
                        <span class="min-w-0 flex-1">
                            <span class="block font-semibold text-gray-900">{{ $quiz->title }}</span>
                            <span class="mt-1 block text-xs text-gray-500">Quiz · {{ $quiz->questions_count }} questions · {{ $quiz->total_marks }} marks</span>
                        </span>
                        <span class="text-sm font-medium text-blue-700">Take quiz →</span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif
</div>
@endsection

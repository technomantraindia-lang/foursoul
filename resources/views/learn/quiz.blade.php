@extends('layouts.app')

@section('title', $quiz->title)

@php
    use App\Enums\AttemptStatus;
@endphp

@section('content')
<div class="max-w-3xl mx-auto px-4 py-8">
    <a href="{{ route('learn.course', $course) }}" class="text-sm text-blue-700 hover:underline">&larr; Back to course</a>

    <h1 class="mt-2 text-2xl font-bold text-gray-900">{{ $quiz->title }}</h1>
    @if ($quiz->description)
        <p class="mt-1 text-sm text-gray-600">{{ $quiz->description }}</p>
    @endif

    <dl class="mt-6 grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
        <div class="rounded-xl border border-gray-200 bg-white p-4">
            <dt class="text-gray-500">Questions</dt>
            <dd class="mt-1 font-semibold text-gray-900">{{ $quiz->questions->count() }}</dd>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4">
            <dt class="text-gray-500">Total marks</dt>
            <dd class="mt-1 font-semibold text-gray-900">{{ (int) $quiz->total_marks }}</dd>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4">
            <dt class="text-gray-500">Passing marks</dt>
            <dd class="mt-1 font-semibold text-gray-900">{{ (int) $quiz->passing_marks }}</dd>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4">
            <dt class="text-gray-500">Duration</dt>
            <dd class="mt-1 font-semibold text-gray-900">{{ $quiz->duration_minutes ? $quiz->duration_minutes.' min' : 'No limit' }}</dd>
        </div>
    </dl>

    <p class="mt-4 text-sm text-gray-600">
        Attempts used: {{ $attemptsUsed }}{{ $quiz->max_attempts ? ' of '.$quiz->max_attempts : '' }}
    </p>

    @if ($attempt && $attempt->status === AttemptStatus::InProgress)
        <div class="mt-6 rounded-xl border border-blue-200 bg-blue-50 p-5">
            <p class="text-sm text-blue-800">You have an attempt in progress.</p>
            <a href="{{ route('learn.quiz.take', [$course, $quiz, $attempt]) }}"
               class="mt-3 inline-block rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                Resume attempt
            </a>
        </div>
    @elseif ($canAttempt)
        <form method="POST" action="{{ route('learn.quiz.start', [$course, $quiz]) }}" class="mt-6">
            @csrf
            <button type="submit"
                    class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                {{ $attemptsUsed > 0 ? 'Start new attempt' : 'Start quiz' }}
            </button>
        </form>
    @else
        <div class="mt-6 rounded-xl border border-yellow-200 bg-yellow-50 p-5 text-sm text-yellow-800">
            You have used all available attempts for this quiz.
        </div>
    @endif
</div>
@endsection

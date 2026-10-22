@extends('layouts.app')

@section('title', 'Quiz result — '.$quiz->title)

@php
    use App\Enums\AttemptStatus;
@endphp

@section('content')
<div class="max-w-3xl mx-auto px-4 py-8">
    <a href="{{ route('learn.quiz.show', [$course, $quiz]) }}" class="text-sm text-blue-700 hover:underline">&larr; Back to quiz</a>

    <h1 class="mt-2 text-2xl font-bold text-gray-900">{{ $quiz->title }}</h1>

    <div class="mt-6 rounded-xl border p-5
        {{ $attempt->passed ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50' }}">
        <p class="text-lg font-semibold {{ $attempt->passed ? 'text-green-800' : 'text-red-800' }}">
            @if ($attempt->status === AttemptStatus::Submitted)
                Submitted — awaiting manual review
            @elseif ($attempt->passed)
                Passed 🎉
            @else
                Not passed
            @endif
        </p>
        <dl class="mt-3 grid grid-cols-3 gap-4 text-sm">
            <div>
                <dt class="text-gray-500">Score</dt>
                <dd class="font-semibold text-gray-900">{{ (float) $attempt->score }} / {{ (int) $quiz->total_marks }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Percentage</dt>
                <dd class="font-semibold text-gray-900">{{ (int) round($attempt->percentage) }}%</dd>
            </div>
            <div>
                <dt class="text-gray-500">Time taken</dt>
                <dd class="font-semibold text-gray-900">{{ $attempt->time_taken_seconds ? gmdate('i:s', (int) $attempt->time_taken_seconds) : '—' }}</dd>
            </div>
        </dl>
    </div>

    <h2 class="mt-8 text-lg font-semibold text-gray-800">Your answers</h2>
    <div class="mt-3 space-y-4">
        @foreach ($answers as $answer)
            <div class="rounded-xl border border-gray-200 bg-white p-4 text-sm">
                <p class="font-medium text-gray-900">{{ $answer->question->question_text ?? 'Question' }}</p>
                <p class="mt-1 text-gray-600">Your answer: {{ $answer->response_text ?? ($answer->selectedOption?->option_text ?? '—') }}</p>
                @if ($answer->question->explanation && $attempt->status !== AttemptStatus::InProgress)
                    <p class="mt-1 text-xs text-gray-500">Explanation: {{ $answer->question->explanation }}</p>
                @endif
            </div>
        @endforeach
    </div>
</div>
@endsection
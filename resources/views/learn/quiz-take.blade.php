@extends('layouts.app')

@section('title', $quiz->title.' — Attempt')

@php
    use App\Enums\QuestionType;
@endphp

@section('content')
<div class="max-w-3xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-gray-900">{{ $quiz->title }}</h1>
    <p class="mt-1 text-sm text-gray-600">
        Attempt #{{ $attempt->attempt_number }}
        @if ($quiz->duration_minutes)
            · {{ $quiz->duration_minutes }} minutes allowed
        @endif
    </p>

    <form method="POST" action="{{ route('learn.quiz.submit', [$course, $quiz, $attempt]) }}" class="mt-6 space-y-6">
        @csrf

        @foreach ($quiz->questions as $question)
            <fieldset class="rounded-xl border border-gray-200 bg-white p-5">
                <legend class="px-2 text-sm font-semibold text-gray-900">
                    {{ $loop->iteration }}. {{ $question->question_text }}
                    <span class="ml-1 font-normal text-gray-500">({{ (int) $question->marks }} marks)</span>
                </legend>

                @php $type = $question->type; @endphp

                @if ($type === QuestionType::SingleChoice || $type === QuestionType::TrueFalse)
                    <div class="mt-3 space-y-2">
                        @foreach ($question->options as $option)
                            <label class="flex items-center gap-2 text-sm">
                                <input type="radio" name="answers[{{ $question->id }}]" value="{{ $option->id }}"
                                       class="rounded border-gray-300">
                                <span>{{ $option->option_text }}</span>
                            </label>
                        @endforeach
                    </div>
                @elseif ($type === QuestionType::MultipleChoice)
                    <div class="mt-3 space-y-2">
                        @foreach ($question->options as $option)
                            <label class="flex items-center gap-2 text-sm">
                                <input type="checkbox" name="answers[{{ $question->id }}][]" value="{{ $option->id }}"
                                       class="rounded border-gray-300">
                                <span>{{ $option->option_text }}</span>
                            </label>
                        @endforeach
                    </div>
                @elseif ($type === QuestionType::Numeric)
                    <input type="number" step="any" name="answers[{{ $question->id }}]"
                           class="mt-3 w-full rounded-lg border-gray-300 text-sm">
                @elseif ($type === QuestionType::ShortAnswer)
                    <input type="text" name="answers[{{ $question->id }}]"
                           class="mt-3 w-full rounded-lg border-gray-300 text-sm">
                @else {{-- Paragraph / future types --}}
                    <textarea name="answers[{{ $question->id }}]" rows="4"
                              class="mt-3 w-full rounded-lg border-gray-300 text-sm"></textarea>
                @endif
            </fieldset>
        @endforeach

        <button type="submit"
                class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
            Submit quiz
        </button>
    </form>
</div>
@endsection

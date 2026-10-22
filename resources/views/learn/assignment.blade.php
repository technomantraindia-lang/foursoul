@extends('layouts.app')

@section('title', $assignment->title)

@php
    use App\Enums\SubmissionStatus;
@endphp

@section('content')
<div class="max-w-3xl mx-auto px-4 py-8">
    <a href="{{ route('learn.course', $course) }}" class="text-sm text-blue-700 hover:underline">&larr; Back to course</a>

    <h1 class="mt-2 text-2xl font-bold text-gray-900">{{ $assignment->title }}</h1>
    @if ($assignment->description)
        <p class="mt-1 text-sm text-gray-600">{{ $assignment->description }}</p>
    @endif

    @if ($assignment->instructions)
        <div class="mt-4 rounded-xl border border-gray-200 bg-white p-4 text-sm text-gray-700">
            <h2 class="font-semibold text-gray-900">Instructions</h2>
            <p class="mt-1 whitespace-pre-line">{{ $assignment->instructions }}</p>
        </div>
    @endif

    <dl class="mt-4 grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
        <div class="rounded-xl border border-gray-200 bg-white p-4">
            <dt class="text-gray-500">Maximum marks</dt>
            <dd class="mt-1 font-semibold text-gray-900">{{ (int) $assignment->max_marks }}</dd>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4">
            <dt class="text-gray-500">Due date</dt>
            <dd class="mt-1 font-semibold text-gray-900">{{ $assignment->due_at ? $assignment->due_at->format('d M Y') : 'No due date' }}</dd>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4">
            <dt class="text-gray-500">Attempts used</dt>
            <dd class="mt-1 font-semibold text-gray-900">{{ $attemptsUsed }}{{ $assignment->max_attempts ? ' of '.$assignment->max_attempts : '' }}</dd>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4">
            <dt class="text-gray-500">Resubmission</dt>
            <dd class="mt-1 font-semibold text-gray-900">{{ $assignment->allow_resubmission ? 'Allowed' : 'Not allowed' }}</dd>
        </div>
    </dl>

    @if (session('success'))
        <div class="mt-6 rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if ($submission)
        <div class="mt-6 rounded-xl border border-gray-200 bg-white p-5">
            <h2 class="text-lg font-semibold text-gray-900">Your submission</h2>
            <p class="mt-2 text-sm text-gray-700 whitespace-pre-line">{{ $submission->submission_text }}</p>

            @if ($submission->file_path)
                <p class="mt-2 text-sm">
                    <a href="{{ Storage::url($submission->file_path) }}" target="_blank"
                       class="text-blue-700 hover:underline">View attached file</a>
                </p>
            @endif

            <p class="mt-3 text-xs text-gray-500">
                Status: {{ $submission->status->getLabel() }}
                · Submitted {{ $submission->created_at->format('d M Y, H:i') }}
                @if ($submission->marks_awarded !== null)
                    · Marks: {{ (float) $submission->marks_awarded }} / {{ (int) $assignment->max_marks }}
                @endif
            </p>

            @if ($submission->feedback)
                <div class="mt-3 rounded-lg bg-gray-50 p-3 text-sm text-gray-700">
                    <span class="font-medium">Feedback:</span> {{ $submission->feedback }}
                </div>
            @endif
        </div>
    @endif

    @php
        $canSubmit = (! $submission)
            || ($assignment->allow_resubmission && in_array($submission->status, [SubmissionStatus::Submitted, SubmissionStatus::Returned], true));
    @endphp

    @if ($canSubmit)
        <form method="POST" action="{{ route('learn.assignment.submit', [$course, $assignment]) }}" enctype="multipart/form-data"
              class="mt-6 rounded-xl border border-gray-200 bg-white p-5 space-y-4">
            @csrf
            <h2 class="text-lg font-semibold text-gray-900">{{ $submission ? 'Resubmit' : 'Submit' }} your work</h2>

            <div>
                <label for="content" class="block text-sm font-medium text-gray-700">Your answer</label>
                <textarea id="content" name="content" rows="6" required
                          class="mt-1 w-full rounded-lg border-gray-300 text-sm">{{ old('content', $submission?->submission_text) }}</textarea>
                @error('content')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="file" class="block text-sm font-medium text-gray-700">Attachment (optional)</label>
                <input type="file" id="file" name="file"
                       class="mt-1 block w-full text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-blue-50 file:px-3 file:py-2 file:text-sm file:text-blue-700">
                @error('file')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <button type="submit"
                    class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                {{ $submission ? 'Resubmit assignment' : 'Submit assignment' }}
            </button>
        </form>
    @elseif ($submission && $submission->status === SubmissionStatus::Graded)
        <div class="mt-6 rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-800">
            This assignment has been graded. No further submission is required.
        </div>
    @else
        <div class="mt-6 rounded-xl border border-yellow-200 bg-yellow-50 p-4 text-sm text-yellow-800">
            You have used all available attempts for this assignment.
        </div>
    @endif
</div>
@endsection

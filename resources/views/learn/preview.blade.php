@extends('layouts.app')

@section('title', 'Preview: ' . $lesson->title)

@php
    use App\Enums\LessonType;
@endphp

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    <a href="{{ route('learn.course', $course) }}" class="text-sm text-blue-700 hover:underline">← {{ $course->title }}</a>

    <div class="mt-2 flex items-center gap-2">
        <h1 class="text-2xl font-bold text-gray-900">{{ $lesson->title }}</h1>
        <span class="text-xs px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 font-medium">Free preview</span>
    </div>

    @if ($lesson->type === LessonType::Video && $lesson->youtube_video_id)
        <div class="mt-4 aspect-video w-full rounded-xl overflow-hidden bg-black">
            <iframe class="w-full h-full" src="https://www.youtube.com/embed/{{ $lesson->youtube_video_id }}"
                    title="{{ $lesson->title }}" frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen></iframe>
        </div>
    @else
        <div class="mt-4 rounded-xl border border-gray-200 bg-white p-6 prose max-w-none">
            {!! $lesson->content !!}
        </div>
    @endif

    <div class="mt-6 rounded-xl border border-blue-200 bg-blue-50 p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <p class="text-sm text-blue-900">Enjoying this lesson? Get full access to the complete course.</p>
        @auth
            <a href="{{ route('learn.course', $course) }}"
               class="inline-block rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 whitespace-nowrap">
                View course
            </a>
        @else
            <a href="{{ route('login') }}"
               class="inline-block rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 whitespace-nowrap">
                Sign in to enroll
            </a>
        @endauth
    </div>
</div>
@endsection
@extends('layouts.app')
@section('title', 'Courses')
@section('content')
<div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
    <div class="max-w-2xl">
        <p class="fs-kicker">Learn with FourSouls</p>
        <h1 class="mt-2 text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">Course catalogue</h1>
        <p class="mt-3 text-base text-gray-600">Explore published courses and continue building skills at your own pace.</p>
    </div>

    @if ($courses->isEmpty())
        <div class="fs-empty-state mt-10" role="status">
            <div class="fs-empty-icon" aria-hidden="true">+</div>
            <h2 class="mt-4 text-lg font-semibold text-gray-900">No courses are available right now.</h2>
            <p class="mt-2 text-sm text-gray-600">Please check back soon for new learning opportunities.</p>
        </div>
    @else
        <div class="mt-10 grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($courses as $course)
                @php($enrollment = $enrollments->get($course->id))
                <article class="fs-course-card">
                    <div class="fs-course-art" aria-hidden="true"><span>FS</span></div>
                    <div class="flex min-h-[15rem] flex-col p-5">
                        <div class="flex items-start justify-between gap-3">
                            <span class="fs-badge">Published</span>
                            @if ($enrollment)
                                <span class="fs-badge fs-badge-success">{{ ucfirst($enrollment->status->value ?? $enrollment->status) }}</span>
                            @endif
                        </div>
                        <h2 class="mt-4 line-clamp-2 text-xl font-semibold text-gray-900">{{ $course->title }}</h2>
                        <p class="mt-2 line-clamp-3 text-sm leading-6 text-gray-600">{{ $course->description ?: 'A focused FourSouls learning experience.' }}</p>
                        <div class="mt-4 flex flex-wrap gap-x-4 gap-y-2 text-xs font-medium text-gray-500">
                            <span>{{ $course->lessons_count }} lessons</span>
                            <span>{{ $course->purchase_mode?->value ? str_replace('_', ' ', ucfirst($course->purchase_mode->value)) : 'Course access' }}</span>
                        </div>
                        <div class="mt-auto flex items-center justify-between gap-3 pt-6">
                            <span class="text-sm font-semibold text-gray-900">{{ $course->effectivePrice($currency->value) !== null ? \App\Support\CurrencyFormatter::format($currency, $course->effectivePrice($currency->value)) : 'View pricing' }}</span>
                            @auth
                                <a class="btn btn-primary btn-sm" href="{{ route('learn.course', $course) }}">{{ $enrollment ? 'Continue learning' : 'View course' }}</a>
                            @else
                                <a class="btn btn-primary btn-sm" href="{{ route('login') }}">Sign in to view</a>
                            @endauth
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</div>
@endsection

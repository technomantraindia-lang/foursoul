@extends('layouts.app')

@section('title', $post->seo_title ?? $post->title)
@section('meta_description', $post->seo_description ?? $post->excerpt)

@section('content')
    <article class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">
        @if ($post->category)
            <span class="text-xs font-semibold uppercase tracking-wide text-indigo-600">{{ $post->category->name }}</span>
        @endif
        <h1 class="mt-2 text-3xl font-extrabold text-gray-900">{{ $post->title }}</h1>
        <p class="mt-3 text-sm text-gray-500">
            @if ($post->author)
                By {{ $post->author->name }} ·
            @endif
            {{ $post->published_at?->format('M j, Y') }}
            @if ($post->reading_time_minutes)
                · {{ $post->reading_time_minutes }} min read
            @endif
        </p>

        @if ($post->featured_image_url)
            <img src="{{ $post->featured_image_url }}" alt="{{ $post->featured_image_alt ?? $post->title }}" class="mt-6 w-full rounded-xl object-cover">
        @endif

        @if ($post->tags->isNotEmpty())
            <div class="mt-4 flex flex-wrap gap-2">
                @foreach ($post->tags as $tag)
                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700">{{ $tag->name }}</span>
                @endforeach
            </div>
        @endif

        <div class="prose prose-indigo mt-8 max-w-none">{!! $post->content !!}</div>
    </article>

    @if ($related->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 pb-16 sm:px-6 lg:px-8">
            <h2 class="text-xl font-bold text-gray-900">Related posts</h2>
            <div class="mt-6 grid gap-6 sm:grid-cols-3">
                @foreach ($related as $relatedPost)
                    <article class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                        <h3 class="font-semibold text-gray-900">
                            <a href="{{ route('blog.show', $relatedPost->slug) }}">{{ $relatedPost->title }}</a>
                        </h3>
                        <p class="mt-1 text-sm text-gray-600">{{ Str::limit($relatedPost->excerpt ?: strip_tags($relatedPost->content), 90) }}</p>
                    </article>
                @endforeach
            </div>
        </section>
    @endif
@endsection
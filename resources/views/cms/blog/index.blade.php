@extends('layouts.app')

@section('title', 'Blog')
@section('meta_description', 'Latest articles, guides and updates.')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-extrabold text-gray-900">Blog</h1>

        <div class="mt-8 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($posts as $post)
                <article class="flex flex-col overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                    @if ($post->featured_image_url)
                        <a href="{{ route('blog.show', $post->slug) }}">
                            <img src="{{ $post->featured_image_url }}" alt="{{ $post->featured_image_alt ?? $post->title }}" class="h-48 w-full object-cover">
                        </a>
                    @endif
                    <div class="flex flex-1 flex-col p-5">
                        @if ($post->category)
                            <span class="text-xs font-semibold uppercase tracking-wide text-indigo-600">{{ $post->category->name }}</span>
                        @endif
                        <h2 class="mt-1 text-lg font-bold text-gray-900">
                            <a href="{{ route('blog.show', $post->slug) }}">{{ $post->title }}</a>
                        </h2>
                        <p class="mt-2 flex-1 text-sm text-gray-600">{{ Str::limit($post->excerpt ?: strip_tags($post->content), 120) }}</p>
                        <p class="mt-4 text-xs text-gray-500">
                            {{ $post->published_at?->format('M j, Y') }}
                            @if ($post->reading_time_minutes)
                                · {{ $post->reading_time_minutes }} min read
                            @endif
                        </p>
                    </div>
                </article>
            @empty
                <p class="text-gray-600">No posts published yet.</p>
            @endforelse
        </div>

        <div class="mt-10">{{ $posts->links() }}</div>
    </div>
@endsection
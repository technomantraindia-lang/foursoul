@extends('layouts.app')

@section('title', $page->seo_title ?? $page->title)
@section('meta_description', $page->seo_description)

@section('content')
    @forelse ($page->contentSections() as $section)
        <section class="py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                @if (! empty($section['heading'] ?? $section['title'] ?? null))
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">{{ $section['heading'] ?? $section['title'] }}</h2>
                @endif
                @foreach (($section['blocks'] ?? [$section]) as $block)
                    @php
                        $type = $block['type'] ?? $block['block_type'] ?? 'text';
                        $data = $block['data'] ?? $block['content'] ?? $block;
                    @endphp
                    <div class="mb-6" data-block="{{ $type }}">
                        @switch($type)
                            @case('hero')
                                <div class="rounded-2xl bg-blue-50 p-10 text-center">
                                    <h1 class="text-4xl font-extrabold text-gray-900">{{ $data['heading'] ?? $page->title }}</h1>
                                    <p class="mt-3 text-lg text-gray-600">{{ $data['subheading'] ?? $page->seo_description }}</p>
                                </div>
                                @break
                            @case('text')
                                <div class="prose max-w-none">{!! $data['html'] ?? e($data['content'] ?? '') !!}</div>
                                @break
                            @case('html')
                                {!! $data['html'] ?? '' !!}
                                @break
                            @default
                                @if (! empty($data['html'] ?? $data['content'] ?? null))
                                    <div class="prose max-w-none">{!! $data['html'] ?? e($data['content']) !!}</div>
                                @endif
                        @endswitch
                    </div>
                @endforeach
            </div>
        </section>
    @empty
        <section class="py-16">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <h1 class="text-3xl font-bold text-gray-900">{{ $page->title }}</h1>
                @if ($page->seo_description)
                    <p class="mt-4 text-gray-600">{{ $page->seo_description }}</p>
                @endif
            </div>
        </section>
    @endforelse
@endsection

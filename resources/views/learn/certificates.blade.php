@extends('layouts.app')

@section('title', 'My certificates')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-gray-900">My certificates</h1>

    @if ($certificates->isEmpty())
        <div class="mt-6 rounded-xl border border-gray-200 bg-white p-6 text-sm text-gray-600">
            You have not earned any certificates yet. Complete a course to earn one.
        </div>
    @else
        <ul class="mt-6 space-y-4">
            @foreach ($certificates as $certificate)
                <li class="flex items-center justify-between rounded-xl border border-gray-200 bg-white p-5">
                    <div>
                        <p class="font-semibold text-gray-900">{{ $certificate->course?->title ?? 'Course' }}</p>
                        <p class="mt-1 text-xs text-gray-500">
                            Code: {{ $certificate->certificate_number }}
                            · Issued {{ optional($certificate->issued_on)->format('d M Y') }}
                        </p>
                    </div>
                    @if ($certificate->course)
                        <a href="{{ route('certificates.show', [$certificate->course, $certificate]) }}"
                           class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                            View
                        </a>
                    @endif
                </li>
            @endforeach
        </ul>
    @endif
</div>
@endsection
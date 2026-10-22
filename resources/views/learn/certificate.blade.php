@extends('layouts.app')

@section('title', 'Certificate — ' . ($course->title ?? ''))

@section('content')
<div class="max-w-3xl mx-auto px-4 py-8">
    <div class="rounded-xl border border-gray-200 bg-white p-8 text-center">
        <p class="text-xs uppercase tracking-widest text-gray-500">Certificate of completion</p>
        <h1 class="mt-4 text-2xl font-bold text-gray-900">{{ $certificate->user->name }}</h1>
        <p class="mt-2 text-sm text-gray-600">has successfully completed</p>
        <p class="mt-1 text-lg font-semibold text-blue-700">{{ $course->title }}</p>

        @if ($certificate->issued_on)
            <p class="mt-4 text-xs text-gray-500">Issued on {{ $certificate->issued_on->format('d M Y') }}</p>
        @endif

        <p class="mt-2 font-mono text-sm text-gray-700">{{ $certificate->certificate_number }}</p>

        <div class="mt-6 flex justify-center gap-3">
            <a href="{{ route('certificates.index') }}"
               class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                Back to certificates
            </a>
            <a href="{{ route('certificates.verify', $certificate->certificate_number) }}" target="_blank"
               class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                Public verification link
            </a>
        </div>
    </div>
</div>
@endsection

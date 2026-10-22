@extends('layouts.app')

@section('title', 'Verify certificate')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-8">
    <h1 class="text-xl font-bold text-gray-900">Certificate verification</h1>

    @if ($certificate)
        <div class="mt-6 rounded-xl border border-green-200 bg-green-50 p-6">
            <p class="font-semibold text-green-800">Valid certificate</p>
            <dl class="mt-4 space-y-2 text-sm text-gray-700">
                <div><dt class="inline font-medium">Code: </dt><dd class="inline font-mono">{{ $certificate->certificate_number }}</dd></div>
                <div><dt class="inline font-medium">Holder: </dt><dd class="inline">{{ $certificate->user->name }}</dd></div>
                <div><dt class="inline font-medium">Course: </dt><dd class="inline">{{ $certificate->course_name_snapshot ?? $certificate->course?->title ?? '—' }}</dd></div>
                <div><dt class="inline font-medium">Issued: </dt><dd class="inline">{{ optional($certificate->issued_on)->format('d M Y') ?? '—' }}</dd></div>
            </dl>
        </div>
    @else
        <div class="mt-6 rounded-xl border border-red-200 bg-red-50 p-6">
            <p class="font-semibold text-red-800">Certificate not found</p>
            <p class="mt-2 text-sm text-gray-600">No valid certificate exists for code <span class="font-mono">{{ $code }}</span>.</p>
        </div>
    @endif
</div>
@endsection

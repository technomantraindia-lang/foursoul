@extends('layouts.app')

@section('title', \App\Support\LegalDocuments::termsTitle())

@section('content')
<div class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">
    <h1 class="text-3xl font-bold tracking-tight text-gray-900">{{ \App\Support\LegalDocuments::termsTitle() }}</h1>
    <p class="mt-2 text-sm text-gray-500">
        Version {{ \App\Support\LegalDocuments::termsVersion() }}
        @if (\App\Support\LegalDocuments::termsUpdatedAt())
            &middot; Last updated {{ \App\Support\LegalDocuments::termsUpdatedAt() }}
        @endif
    </p>

    <div class="prose prose-blue mt-8 max-w-none text-sm leading-6 text-gray-700">
        <p>
            The complete Terms of Service will be published here before launch.
            By creating an account you agree that your use of {{ config('app.name', 'Foursouls') }}
            is governed by these terms; the version you accepted at registration
            is recorded on your account.
        </p>
        <p class="mt-4">
            Questions? Contact
            <a href="mailto:{{ config('legal.support_email') }}" class="font-semibold text-blue-600 hover:text-blue-700">{{ config('legal.support_email') }}</a>.
        </p>
    </div>

    <p class="mt-10 text-sm">
        <a href="{{ url()->previous() ?: route('home') }}" class="font-semibold text-blue-600 hover:text-blue-700">&larr; Back</a>
    </p>
</div>
@endsection

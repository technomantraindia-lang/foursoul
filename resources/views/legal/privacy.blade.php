@extends('layouts.app')

@section('title', \App\Support\LegalDocuments::privacyTitle())

@section('content')
<div class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">
    <h1 class="text-3xl font-bold tracking-tight text-gray-900">{{ \App\Support\LegalDocuments::privacyTitle() }}</h1>
    <p class="mt-2 text-sm text-gray-500">Version {{ \App\Support\LegalDocuments::privacyVersion() }} @if (\App\Support\LegalDocuments::privacyUpdatedAt()) &middot; Last updated {{ \App\Support\LegalDocuments::privacyUpdatedAt() }} @endif</p>
    <div class="prose prose-blue mt-8 max-w-none text-sm leading-6 text-gray-700">
        <p>The complete Privacy Policy will be published here before launch. We collect only the data required to operate your account and record which policy version you accepted at registration.</p>
        <p class="mt-4">Questions? Contact <a href="mailto:{{ config('legal.support_email') }}" class="font-semibold text-blue-600 hover:text-blue-700">{{ config('legal.support_email') }}</a>.</p>
    </div>
    <p class="mt-10 text-sm"><a href="{{ url()->previous() ?: route('home') }}" class="font-semibold text-blue-600 hover:text-blue-700">&larr; Back</a></p>
</div>
@endsection

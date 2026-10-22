<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Foursouls') }}{{ isset($pageTitle) ? ' | ' . $pageTitle : '' }}</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50 text-gray-900">
    @include('layouts.partials.header')

    @include('layouts.partials.flash')

    <main class="min-h-[calc(100vh-4rem-14rem)]">
        @yield('content')
    </main>

    @include('layouts.partials.footer')
</body>
</html>
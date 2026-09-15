<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $settings['company_name'] ?? config('app.name'))</title>
    <meta name="description" content="@yield('meta_description', $settings['company_description_'.app()->getLocale()] ?? ($settings['company_description'] ?? ''))">

    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    @if (is_file(public_path('images/brand/qcore-logo.png')))
        <link rel="apple-touch-icon" href="{{ asset('images/brand/qcore-logo.png') }}">
    @endif

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=cairo:400,500,600,700|inter:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="min-h-screen flex flex-col">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:start-4 focus:top-4 focus:z-50 focus:rounded-lg focus:bg-brand-cyan focus:px-4 focus:py-2 focus:text-brand-navy">
        {{ __('Skip to content') }}
    </a>

    @include('public.partials.header')

    <main id="main-content" class="flex-1">
        @yield('content')
    </main>

    @include('public.partials.footer')
    @include('partials.app-flash')
    @stack('scripts')
</body>
</html>

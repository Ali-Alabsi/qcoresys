<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('Customer Portal')) — QCoreSys</title>
    <link href="https://fonts.bunny.net/css?family=cairo:400,500,600,700|inter:400,500,600,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50">
<header class="bg-brand-navy text-white">
    <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-4 px-4 py-5 sm:px-6">
        <a href="{{ route('portal.dashboard') }}" class="text-xl font-bold">QCore<span class="text-brand-cyan">Sys</span> <span class="text-sm font-normal text-slate-300">{{ __('Portal') }}</span></a>
        <nav class="flex items-center gap-2 text-sm">
            @php
                $portalLinks = [
                    ['portal.dashboard', __('Dashboard'), ['portal.dashboard']],
                    ['portal.requests.index', __('Requests'), ['portal.requests.*']],
                    ['portal.quotations.index', __('Quotations'), ['portal.quotations.*']],
                ];
            @endphp
            @foreach ($portalLinks as [$route, $label, $activePatterns])
                <a href="{{ route($route) }}" class="rounded-lg px-3 py-2 {{ request()->routeIs(...$activePatterns) ? 'bg-brand-cyan text-brand-navy' : 'hover:bg-white/10' }}">{{ $label }}</a>
            @endforeach
            <a href="{{ route('locale.switch', app()->getLocale() === 'ar' ? 'en' : 'ar') }}" class="rounded-lg px-3 py-2 hover:bg-white/10">{{ app()->getLocale() === 'ar' ? 'EN' : 'AR' }}</a>
            <form method="POST" action="{{ route('portal.logout') }}">@csrf<button class="rounded-lg px-3 py-2 hover:bg-white/10">{{ __('Logout') }}</button></form>
        </nav>
    </div>
</header>
<main class="mx-auto max-w-7xl px-4 py-8 sm:px-6">
    @yield('content')
</main>
@include('partials.app-flash')
</body>
</html>

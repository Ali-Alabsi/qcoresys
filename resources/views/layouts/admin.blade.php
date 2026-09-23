<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('Administration')) — QCoreSys</title>
    <link href="https://fonts.bunny.net/css?family=cairo:400,500,600,700|inter:400,500,600,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50" x-data="{menu:false}">
@php
    $navSections = \App\Support\AdminNavigation::visibleSections(auth()->user());
    $homeUrl = \App\Support\AdminNavigation::homeUrl(auth()->user());
@endphp
<div class="min-h-screen lg:flex">
    <aside
        class="z-30 flex w-72 shrink-0 flex-col bg-brand-navy text-white transition max-lg:fixed max-lg:inset-y-0 lg:sticky lg:top-0 lg:h-screen lg:translate-x-0"
        :class="menu ? 'translate-x-0' : '{{ app()->getLocale() === 'ar' ? 'max-lg:translate-x-full' : 'max-lg:-translate-x-full' }}'"
    >
        <div class="flex h-20 shrink-0 items-center justify-between border-b border-white/10 px-6">
            <a href="{{ $homeUrl }}" class="text-xl font-bold">QCore<span class="text-brand-cyan">Sys</span></a>
            <button class="lg:hidden" @click="menu=false">✕</button>
        </div>
        <nav class="min-h-0 flex-1 space-y-4 overflow-y-auto p-4 text-sm">
            @foreach ($navSections as $section)
                <div>
                    <div class="mb-1 px-4 text-[10px] font-bold uppercase tracking-wider text-slate-500">{{ $section['label'] }}</div>
                    <div class="space-y-1">
                        @foreach ($section['links'] as $link)
                            <a href="{{ route($link['route']) }}" class="block rounded-xl px-4 py-3 {{ request()->routeIs(...$link['active']) ? 'bg-brand-cyan text-brand-navy' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">{{ $link['label'] }}</a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </nav>
    </aside>
    <div class="min-w-0 flex-1">
        <header class="flex h-20 items-center justify-between border-b border-slate-200 bg-white px-4 sm:px-8">
            <button class="text-2xl lg:hidden" @click="menu=true">☰</button>
            <div>
                <div class="font-semibold text-brand-navy">{{ auth()->user()->full_name }}</div>
                <div class="text-xs text-slate-500">{{ auth()->user()->email }}</div>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('locale.switch', app()->getLocale() === 'ar' ? 'en' : 'ar') }}" class="btn-secondary !px-3 !py-2">{{ app()->getLocale() === 'ar' ? 'EN' : 'AR' }}</a>
                <form method="POST" action="{{ route('admin.logout') }}">@csrf<button class="btn-navy !px-4 !py-2">{{ __('Logout') }}</button></form>
            </div>
        </header>
        <main class="p-4 sm:p-8">
            @yield('content')
        </main>
    </div>
</div>
@include('partials.app-flash')
@stack('scripts')
</body>
</html>

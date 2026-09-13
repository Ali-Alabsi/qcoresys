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
<div class="min-h-screen lg:flex">
    <aside class="fixed inset-y-0 z-30 w-72 bg-brand-navy text-white transition lg:static lg:translate-x-0" :class="menu ? 'translate-x-0' : '{{ app()->getLocale() === 'ar' ? 'translate-x-full' : '-translate-x-full' }}'">
        <div class="flex h-20 items-center justify-between border-b border-white/10 px-6">
            <a href="{{ route('admin.dashboard') }}" class="text-xl font-bold">QCore<span class="text-brand-cyan">Sys</span></a>
            <button class="lg:hidden" @click="menu=false">✕</button>
        </div>
        <nav class="space-y-1 p-4 text-sm">
            @php
                $links = [
                    ['customers.view', 'admin.dashboard', __('Dashboard'), ['admin.dashboard']],
                    ['customers.view', 'admin.customers.index', __('Customers'), ['admin.customers.*']],
                    ['customer_requests.view', 'admin.customer-requests.index', __('Requests'), ['admin.customer-requests.*']],
                    ['quotations.view', 'admin.quotations.index', __('Quotations'), ['admin.quotations.*']],
                    ['accounts.view', 'admin.accounts.index', __('Accounts'), ['admin.accounts.*']],
                    ['journals.view', 'admin.journals.index', __('Financial operations'), ['admin.journals.index', 'admin.journals.show', 'admin.journals.create']],
                    ['exchange_rates.view', 'admin.exchange-rates.index', __('Exchange rates'), ['admin.exchange-rates.*']],
                    ['services_catalog.view', 'admin.services.index', __('Services'), ['admin.services.*']],
                    ['portfolio.view', 'admin.portfolio-projects.index', __('Portfolio'), ['admin.portfolio-projects.*']],
                    ['settings.view', 'admin.settings.edit', __('Settings'), ['admin.settings.*']],
                ];
            @endphp
            @foreach ($links as [$permission, $route, $label, $activePatterns])
                @if (auth()->user()->hasPermission($permission))
                    <a href="{{ route($route) }}" class="block rounded-xl px-4 py-3 {{ request()->routeIs(...$activePatterns) ? 'bg-brand-cyan text-brand-navy' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">{{ $label }}</a>
                @endif
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
            @if (session('status'))<div class="mb-5 rounded-xl border border-cyan-200 bg-cyan-50 px-4 py-3 text-sm">{{ session('status') }}</div>@endif
            @if ($errors->any())<div class="mb-5 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800"><ul class="list-disc ps-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            @yield('content')
        </main>
    </div>
</div>
@stack('scripts')
</body>
</html>

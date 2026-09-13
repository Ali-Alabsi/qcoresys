@php
    $companyName = $settings['company_name'] ?? 'QCoreSys';
    $navItems = [
        ['route' => 'home', 'label' => __('Home')],
        ['route' => 'services.index', 'label' => __('Services')],
        ['route' => 'solutions', 'label' => __('Solutions')],
        ['route' => 'about', 'label' => __('About')],
        ['route' => 'contact', 'label' => __('Contact')],
    ];
@endphp

<header class="sticky top-0 z-40 border-b border-brand-border/80 bg-white/90 backdrop-blur-md" x-data="{ open: false }">
    <div class="container-public flex h-16 items-center justify-between gap-4 lg:h-20">
        <a href="{{ route('home') }}" class="flex items-center gap-3" aria-label="{{ $companyName }}">
            @include('public.partials.logo', [
                'settings' => $settings,
                'companyName' => $companyName,
                'heightClass' => 'h-9 lg:h-11',
                'showName' => true,
                'nameClass' => 'hidden text-base font-semibold text-brand-navy sm:inline',
            ])
        </a>

        <nav class="hidden items-center gap-1 lg:flex" aria-label="Primary">
            @foreach ($navItems as $item)
                <a href="{{ route($item['route']) }}"
                   class="rounded-lg px-3 py-2 text-sm font-medium transition {{ request()->routeIs($item['route']) || request()->routeIs(str_replace('.index', '.*', $item['route'])) ? 'bg-brand-bg text-brand-navy' : 'text-brand-secondary hover:bg-brand-bg hover:text-brand-navy' }}">
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>

        <div class="hidden items-center gap-3 lg:flex">
            <div class="flex items-center rounded-full border border-brand-border p-1 text-xs font-semibold" role="group" aria-label="Language">
                <a href="{{ route('locale.switch', 'ar') }}" class="rounded-full px-2.5 py-1 {{ app()->getLocale() === 'ar' ? 'bg-brand-navy text-white' : 'text-brand-secondary' }}">AR</a>
                <a href="{{ route('locale.switch', 'en') }}" class="rounded-full px-2.5 py-1 {{ app()->getLocale() === 'en' ? 'bg-brand-navy text-white' : 'text-brand-secondary' }}">EN</a>
            </div>
            <a href="{{ route('consultation') }}" class="btn-primary !px-4 !py-2.5">{{ __('Request Consultation') }}</a>
        </div>

        <button type="button"
                class="inline-flex items-center justify-center rounded-lg border border-brand-border p-2 text-brand-navy lg:hidden"
                @click="open = !open"
                :aria-expanded="open.toString()"
                aria-controls="mobile-nav"
                aria-label="{{ __('Open menu') }}">
            <svg x-show="!open" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            <svg x-cloak x-show="open" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>

    <div id="mobile-nav" x-cloak x-show="open" x-transition class="border-t border-brand-border bg-white lg:hidden">
        <nav class="container-public flex flex-col gap-1 py-4" aria-label="Mobile">
            @foreach ($navItems as $item)
                <a href="{{ route($item['route']) }}" class="rounded-lg px-3 py-3 text-sm font-medium text-brand-navy hover:bg-brand-bg">{{ $item['label'] }}</a>
            @endforeach
            <div class="mt-2 flex items-center justify-between gap-3 border-t border-brand-border pt-4">
                <div class="flex items-center rounded-full border border-brand-border p-1 text-xs font-semibold">
                    <a href="{{ route('locale.switch', 'ar') }}" class="rounded-full px-2.5 py-1 {{ app()->getLocale() === 'ar' ? 'bg-brand-navy text-white' : 'text-brand-secondary' }}">AR</a>
                    <a href="{{ route('locale.switch', 'en') }}" class="rounded-full px-2.5 py-1 {{ app()->getLocale() === 'en' ? 'bg-brand-navy text-white' : 'text-brand-secondary' }}">EN</a>
                </div>
                <a href="{{ route('consultation') }}" class="btn-primary !px-4 !py-2">{{ __('Request Consultation') }}</a>
            </div>
        </nav>
    </div>
</header>

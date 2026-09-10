@extends('layouts.public')

@section('title', ($settings['hero_title_'.app()->getLocale()] ?? __('Services')).' | '.($settings['company_name'] ?? 'QCoreSys'))

@section('content')
@php
    $locale = app()->getLocale();
    $heroTitle = $settings['hero_title_'.$locale] ?? __('Services');
    $heroSubtitle = $settings['hero_subtitle_'.$locale] ?? null;
    $trustTitle = $settings['trust_title_'.$locale] ?? __('Technology Expertise Focused on Results');
    $trustBody = $settings['trust_body_'.$locale] ?? null;
    $ctaTitle = $settings['cta_title_'.$locale] ?? __('Have a Technology Challenge?');
    $ctaSubtitle = $settings['cta_subtitle_'.$locale] ?? __("Let's Talk About It.");
@endphp

<section class="relative overflow-hidden bg-brand-navy text-white">
    <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(0,210,255,0.25),transparent_45%)]"></div>
    <div class="container-public section-padding relative">
        <div class="max-w-3xl">
            <p class="mb-4 inline-flex rounded-full border border-brand-cyan/30 bg-brand-cyan/10 px-3 py-1 text-xs font-semibold text-brand-cyan">{{ __('Services') }}</p>
            <h1 class="text-3xl font-bold leading-tight sm:text-4xl lg:text-5xl">{{ $heroTitle }}</h1>
            <p class="mt-5 max-w-2xl text-base leading-relaxed text-white/75 sm:text-lg">{{ $heroSubtitle }}</p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="{{ route('consultation') }}" class="btn-primary">{{ __('Request a Consultation') }}</a>
                <a href="{{ route('services.index') }}" class="btn-secondary !border-white/20 !bg-transparent !text-white hover:!border-brand-cyan hover:!text-brand-cyan">{{ __('Explore Our Services') }}</a>
            </div>
        </div>
    </div>
</section>

@if ($featured->isNotEmpty())
<section class="section-padding">
    <div class="container-public">
        <div class="mb-10 flex items-end justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-brand-navy sm:text-3xl">{{ __('Featured Services') }}</h2>
            </div>
            <a href="{{ route('services.index') }}" class="hidden text-sm font-semibold text-brand-navy hover:text-brand-cyan sm:inline">{{ __('Explore Our Services') }}</a>
        </div>
        <div class="grid gap-6 lg:grid-cols-12">
            @foreach ($featured as $index => $service)
                <div class="{{ $index === 0 ? 'lg:col-span-7' : ($index === 1 ? 'lg:col-span-5' : 'lg:col-span-4') }}">
                    @include('public.partials.service-card', ['service' => $service])
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

@if ($categories->isNotEmpty())
<section class="border-y border-brand-border bg-white section-padding">
    <div class="container-public">
        <h2 class="mb-8 text-2xl font-bold text-brand-navy">{{ __('Solutions') }}</h2>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @foreach ($categories as $category)
                <a href="{{ route('services.index', ['category' => $category->slug]) }}" class="card-premium group block !p-5" data-reveal>
                    <div class="mb-3 h-1.5 w-10 rounded-full bg-brand-cyan transition group-hover:w-14"></div>
                    <h3 class="font-semibold text-brand-navy">{{ $category->localized_name }}</h3>
                    @if ($category->localized_description)
                        <p class="mt-2 text-sm text-brand-secondary">{{ \Illuminate\Support\Str::limit($category->localized_description, 90) }}</p>
                    @endif
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif

<section class="section-padding">
    <div class="container-public">
        <div class="rounded-3xl border border-brand-border bg-white p-8 sm:p-12" data-reveal>
            <h2 class="text-2xl font-bold text-brand-navy sm:text-3xl">{{ $trustTitle }}</h2>
            @if ($trustBody)
                <p class="mt-4 max-w-3xl text-brand-secondary">{{ $trustBody }}</p>
            @endif
        </div>
    </div>
</section>

@if ($portfolio->total() > 0)
<section class="section-padding bg-white">
    <div class="container-public">
        <div class="mb-8 flex items-end justify-between">
            <h2 class="text-2xl font-bold text-brand-navy sm:text-3xl">{{ __('Our Work') }}</h2>
            <a href="{{ route('portfolio.index') }}" class="text-sm font-semibold text-brand-navy hover:text-brand-cyan">{{ __('Portfolio') }}</a>
        </div>
        <div class="grid gap-6 md:grid-cols-3">
            @foreach ($portfolio as $project)
                <a href="{{ route('portfolio.show', $project->slug) }}" class="card-premium block" data-reveal>
                    <h3 class="font-semibold text-brand-navy">{{ $project->localized_title }}</h3>
                    <p class="mt-2 text-sm text-brand-secondary">{{ \Illuminate\Support\Str::limit($project->localized_short_description, 100) }}</p>
                    @if ($project->localized_industry)
                        <span class="badge-tech mt-4">{{ $project->localized_industry }}</span>
                    @endif
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif

<section class="section-padding">
    <div class="container-public">
        <div class="overflow-hidden rounded-3xl bg-brand-navy px-8 py-12 text-white sm:px-12" data-reveal>
            <h2 class="text-2xl font-bold sm:text-3xl">{{ $ctaTitle }}</h2>
            <p class="mt-3 text-lg text-white/75">{{ $ctaSubtitle }}</p>
            <a href="{{ route('consultation') }}" class="btn-primary mt-8 inline-flex">{{ __('Book a Consultation') }}</a>
        </div>
    </div>
</section>
@endsection

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
    $heroImageUrl = is_file(public_path('images/paner.png'))
        ? asset('images/paner.png')
        : null;
@endphp

<section
    @class([
        'relative overflow-hidden bg-brand-navy text-white',
        'bg-cover bg-no-repeat bg-right rtl:bg-left' => $heroImageUrl,
    ])
    @if ($heroImageUrl)
        style="background-image: url('{{ $heroImageUrl }}');"
    @endif
>
    @if ($heroImageUrl)
        <div class="pointer-events-none absolute inset-0 bg-brand-navy/35"></div>
    @else
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(0,210,255,0.25),transparent_45%)]"></div>
    @endif
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

@include('public.partials.service-highlights')

@if ($featured->isNotEmpty())
<section class="section-padding !pt-10 !pb-6 lg:!pb-8">
    <div class="container-public">
        <div class="mb-10 flex items-end justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-brand-navy sm:text-3xl">{{ __('Our Solutions and Services') }}</h2>
            </div>
            <a href="{{ route('services.index') }}" class="hidden text-sm font-semibold text-brand-navy hover:text-brand-cyan sm:inline">{{ __('Explore Our Services') }}</a>
        </div>
        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($featured as $service)
                @include('public.partials.service-card', ['service' => $service])
            @endforeach
        </div>
    </div>
</section>
@endif

<section class="px-4 py-6 sm:px-6 sm:py-8 lg:px-8">
    <div class="container-public">
        <div class="rounded-3xl border border-brand-border bg-white p-8 sm:p-12" data-reveal>
            <h2 class="text-2xl font-bold text-brand-navy sm:text-3xl">{{ $trustTitle }}</h2>
            @if ($trustBody)
                <p class="mt-4 max-w-3xl text-brand-secondary">{{ $trustBody }}</p>
            @endif
        </div>
    </div>
</section>

<section class="section-padding !pt-6 lg:!pt-8">
    <div class="container-public">
        <div class="overflow-hidden rounded-3xl bg-brand-navy px-8 py-12 text-white sm:px-12" data-reveal>
            <h2 class="text-2xl font-bold sm:text-3xl">{{ $ctaTitle }}</h2>
            <p class="mt-3 max-w-3xl text-lg text-white/75">{{ $ctaSubtitle }}</p>
            <a href="{{ route('consultation') }}" class="btn-primary mt-8 inline-flex">{{ __('Book a Consultation') }}</a>
        </div>
    </div>
</section>
@endsection

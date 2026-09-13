@extends('layouts.public')

@section('title', ($service->localized_meta_title ?: $service->localized_name).' | '.($settings['company_name'] ?? 'QCoreSys'))
@section('meta_description', $service->localized_meta_description ?: $service->localized_short_description)

@section('content')
@php
    use App\Enums\PricingType;
    $pricingLabel = match ($service->pricing_type) {
        PricingType::Fixed => __('Fixed Price'),
        PricingType::StartingFrom => __('Starting From'),
        PricingType::Custom => __('Custom Pricing'),
        PricingType::ContactUs => __('Contact Us'),
        PricingType::QuoteRequired => __('Request a Quote'),
        default => __('Contact Us'),
    };
    $showPrice = in_array($service->pricing_type, [PricingType::Fixed, PricingType::StartingFrom], true) && $service->starting_price !== null;
@endphp

<section class="bg-brand-navy text-white">
    <div class="container-public section-padding">
        <nav class="mb-6 text-sm text-white/60" aria-label="Breadcrumb">
            <ol class="flex flex-wrap items-center gap-2">
                <li><a href="{{ route('home') }}" class="hover:text-white">{{ __('Home') }}</a></li>
                <li aria-hidden="true">/</li>
                <li><a href="{{ route('services.index') }}" class="hover:text-white">{{ __('Services') }}</a></li>
                <li aria-hidden="true">/</li>
                <li class="text-white">{{ $service->localized_name }}</li>
            </ol>
        </nav>
        <div class="grid gap-8 lg:grid-cols-12 lg:items-end">
            <div class="lg:col-span-8">
                @if ($service->category)
                    <span class="mb-4 inline-flex rounded-full border border-brand-cyan/30 bg-brand-cyan/10 px-3 py-1 text-xs font-semibold text-brand-cyan">{{ $service->category->localized_name }}</span>
                @endif
                <h1 class="text-3xl font-bold sm:text-4xl lg:text-5xl">{{ $service->localized_name }}</h1>
                <p class="mt-5 max-w-2xl text-lg text-white/75">{{ $service->localized_short_description }}</p>
            </div>
            <div class="lg:col-span-4">
                <div class="rounded-2xl border border-white/10 bg-white/5 p-5 backdrop-blur">
                    <p class="text-sm text-white/60">{{ $pricingLabel }}</p>
                    @if ($showPrice)
                        <p class="mt-1 text-2xl font-bold text-brand-cyan">{{ $service->currency?->symbol }}{{ number_format((float) $service->starting_price, 0) }}</p>
                    @endif
                    <a href="{{ route('services.request', $service->slug) }}" class="btn-primary mt-5 w-full">{{ __('Request This Service') }}</a>
                </div>
            </div>
        </div>
    </div>
</section>

@if ($service->localized_overview || $service->localized_description)
<section class="section-padding">
    <div class="container-public max-w-4xl">
        <h2 class="text-2xl font-bold text-brand-navy">{{ __('What is this service?') }}</h2>
        <div class="prose prose-slate mt-4 max-w-none text-brand-secondary leading-relaxed">
            {!! nl2br(e($service->localized_overview ?: $service->localized_description)) !!}
        </div>
    </div>
</section>
@endif

@if ($service->features->isNotEmpty())
<section class="section-padding bg-white">
    <div class="container-public">
        <h2 class="mb-8 text-2xl font-bold text-brand-navy">{{ __('What We Provide') }}</h2>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($service->features as $feature)
                <div class="card-premium !p-5" data-reveal>
                    <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-lg bg-brand-cyan/10 text-brand-cyan">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h3 class="font-semibold text-brand-navy">{{ $feature->localized_title }}</h3>
                    @if ($feature->localized_description)
                        <p class="mt-2 text-sm text-brand-secondary">{{ $feature->localized_description }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

@if ($service->processSteps->isNotEmpty())
<section class="section-padding">
    <div class="container-public">
        <h2 class="mb-10 text-2xl font-bold text-brand-navy">{{ __('How We Work') }}</h2>
        <ol class="relative space-y-6 border-s-2 border-brand-border ms-3 ps-8">
            @foreach ($service->processSteps as $step)
                <li class="relative" data-reveal>
                    <span class="absolute -start-[2.55rem] flex h-8 w-8 items-center justify-center rounded-full bg-brand-navy text-xs font-bold text-brand-cyan">{{ str_pad((string) $step->step_number, 2, '0', STR_PAD_LEFT) }}</span>
                    <h3 class="font-semibold text-brand-navy">{{ $step->localized_title }}</h3>
                    @if ($step->localized_description)
                        <p class="mt-1 text-sm text-brand-secondary">{{ $step->localized_description }}</p>
                    @endif
                </li>
            @endforeach
        </ol>
    </div>
</section>
@endif

@if ($service->technologies->isNotEmpty())
<section class="section-padding bg-white">
    <div class="container-public">
        <h2 class="mb-6 text-2xl font-bold text-brand-navy">{{ __('Technologies') }}</h2>
        <div class="flex flex-wrap gap-3">
            @foreach ($service->technologies as $tech)
                <span class="badge-tech !px-4 !py-2">{{ $tech->localized_name }}</span>
            @endforeach
        </div>
    </div>
</section>
@endif

@if ($service->benefits->isNotEmpty())
<section class="section-padding">
    <div class="container-public">
        <h2 class="mb-8 text-2xl font-bold text-brand-navy">{{ __('Why Choose This Service?') }}</h2>
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($service->benefits as $benefit)
                <div class="card-premium" data-reveal>
                    <h3 class="font-semibold text-brand-navy">{{ $benefit->localized_title }}</h3>
                    @if ($benefit->localized_description)
                        <p class="mt-2 text-sm text-brand-secondary">{{ $benefit->localized_description }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

@if ($service->faqs->isNotEmpty())
<section class="section-padding" x-data="{ open: null }">
    <div class="container-public max-w-3xl">
        <h2 class="mb-8 text-2xl font-bold text-brand-navy">{{ __('Frequently Asked Questions') }}</h2>
        <div class="space-y-3">
            @foreach ($service->faqs as $index => $faq)
                <div class="rounded-2xl border border-brand-border bg-white">
                    <button type="button" class="flex w-full items-center justify-between gap-4 px-5 py-4 text-start font-medium text-brand-navy" @click="open = open === {{ $index }} ? null : {{ $index }}" :aria-expanded="(open === {{ $index }}).toString()">
                        <span>{{ $faq->localized_question }}</span>
                        <span class="text-brand-cyan" x-text="open === {{ $index }} ? '−' : '+'"></span>
                    </button>
                    <div x-cloak x-show="open === {{ $index }}" x-transition class="border-t border-brand-border px-5 py-4 text-sm text-brand-secondary">
                        {{ $faq->localized_answer }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<section class="section-padding bg-white">
    <div class="container-public">
        <div class="rounded-3xl bg-brand-navy px-8 py-12 text-white sm:flex sm:items-center sm:justify-between sm:gap-8">
            <div>
                <h2 class="text-2xl font-bold">{{ __('Need This Service?') }}</h2>
                <p class="mt-3 max-w-xl text-white/75">{{ __('Tell us what you need and our team will contact you to discuss your requirements.') }}</p>
            </div>
            <a href="{{ route('services.request', $service->slug) }}" class="btn-primary mt-6 shrink-0 sm:mt-0">{{ __('Request Service') }}</a>
        </div>
    </div>
</section>

<div class="fixed inset-x-0 bottom-0 z-30 border-t border-brand-border bg-white/95 p-3 backdrop-blur lg:hidden">
    <a href="{{ route('services.request', $service->slug) }}" class="btn-primary w-full">{{ __('Request This Service') }}</a>
</div>
@endsection

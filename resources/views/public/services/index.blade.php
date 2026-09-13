@extends('layouts.public')

@section('title', __('Services').' | '.($settings['company_name'] ?? 'QCoreSys'))

@section('content')
@php
    $locale = app()->getLocale();
    $heroTitle = $settings['services_hero_title_'.$locale] ?? $settings['hero_title_'.$locale] ?? __('Services');
    $heroSubtitle = $settings['services_hero_subtitle_'.$locale] ?? $settings['hero_subtitle_'.$locale] ?? null;
    $serviceTypes = ['CONSULTING', 'DEVELOPMENT', 'INFRASTRUCTURE', 'SECURITY', 'CLOUD', 'SUPPORT', 'TRAINING'];
    $pricingTypes = ['FIXED', 'STARTING_FROM', 'CUSTOM', 'CONTACT_US', 'QUOTE_REQUIRED'];
@endphp

<section class="bg-brand-navy text-white">
    <div class="container-public section-padding !pb-12 !pt-16">
        <h1 class="text-3xl font-bold sm:text-4xl">{{ $heroTitle }}</h1>
        @if ($heroSubtitle)
            <p class="mt-4 max-w-2xl text-white/75">{{ $heroSubtitle }}</p>
        @endif
        <div class="mt-8 flex flex-wrap gap-3">
            <a href="{{ route('consultation') }}" class="btn-primary">{{ __('Request a Consultation') }}</a>
            <a href="#service-catalog" class="btn-secondary !border-white/20 !bg-transparent !text-white">{{ __('Explore Our Services') }}</a>
        </div>
    </div>
</section>

@if ($featured->isNotEmpty())
<section class="section-padding !pb-8">
    <div class="container-public">
        <h2 class="mb-8 text-2xl font-bold text-brand-navy">{{ __('Featured Services') }}</h2>
        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($featured as $service)
                @include('public.partials.service-card', ['service' => $service])
            @endforeach
        </div>
    </div>
</section>
@endif

<section id="service-catalog" class="section-padding !pt-8">
    <div class="container-public">
        <form method="GET" action="{{ route('services.index') }}" class="mb-8 rounded-2xl border border-brand-border bg-white p-4 sm:p-6" role="search">
            <div class="grid gap-4 lg:grid-cols-12">
                <div class="lg:col-span-4">
                    <label class="label-public" for="search">{{ __('Search services...') }}</label>
                    <input id="search" name="search" type="search" value="{{ request('search') }}" class="input-public" placeholder="{{ __('Search services...') }}">
                </div>
                <div class="lg:col-span-2">
                    <label class="label-public" for="category">{{ __('Category') }}</label>
                    <select id="category" name="category" class="input-public">
                        <option value="">{{ __('All') }}</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->slug }}" @selected(request('category') === $category->slug)>{{ $category->localized_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="lg:col-span-2">
                    <label class="label-public" for="technology">{{ __('Technology') }}</label>
                    <select id="technology" name="technology" class="input-public">
                        <option value="">{{ __('All') }}</option>
                        @foreach ($technologies as $technology)
                            <option value="{{ $technology->slug }}" @selected(request('technology') === $technology->slug)>{{ $technology->localized_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="lg:col-span-2">
                    <label class="label-public" for="service_type">{{ __('Service Type') }}</label>
                    <select id="service_type" name="service_type" class="input-public">
                        <option value="">{{ __('All') }}</option>
                        @foreach ($serviceTypes as $type)
                            <option value="{{ $type }}" @selected(request('service_type') === $type)>{{ __(ucwords(strtolower(str_replace('_', ' ', $type)))) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="lg:col-span-2">
                    <label class="label-public" for="pricing_type">{{ __('Pricing Type') }}</label>
                    <select id="pricing_type" name="pricing_type" class="input-public">
                        <option value="">{{ __('All') }}</option>
                        @foreach ($pricingTypes as $type)
                            <option value="{{ $type }}" @selected(request('pricing_type') === $type)>{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="mt-4 flex flex-wrap gap-3">
                <button type="submit" class="btn-navy !py-2.5">{{ __('Filter') }}</button>
                <a href="{{ route('services.index') }}" class="btn-secondary !py-2.5">{{ __('Clear filters') }}</a>
            </div>
        </form>

        @if ($categories->isNotEmpty())
            <div class="mb-8 flex gap-2 overflow-x-auto pb-2" role="navigation" aria-label="{{ __('Category') }}">
                <a href="{{ route('services.index') }}" class="whitespace-nowrap rounded-full px-4 py-2 text-sm font-medium {{ !request('category') ? 'bg-brand-navy text-white' : 'bg-white text-brand-secondary border border-brand-border' }}">{{ __('All') }}</a>
                @foreach ($categories as $category)
                    <a href="{{ route('services.index', ['category' => $category->slug]) }}"
                       class="whitespace-nowrap rounded-full px-4 py-2 text-sm font-medium {{ request('category') === $category->slug ? 'bg-brand-navy text-white' : 'bg-white text-brand-secondary border border-brand-border' }}">
                        {{ $category->localized_name }}
                    </a>
                @endforeach
            </div>
        @endif

        @if ($services->isEmpty())
            <div class="rounded-2xl border border-dashed border-brand-border bg-white px-6 py-16 text-center">
                <p class="text-brand-secondary">{{ __('No services are currently available.') }}</p>
            </div>
        @else
            <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($services as $service)
                    @include('public.partials.service-card', ['service' => $service])
                @endforeach
            </div>
            <div class="mt-10">
                {{ $services->links() }}
            </div>
        @endif
    </div>
</section>

@php
    $trustTitle = $settings['trust_title_'.$locale] ?? __('Technology Expertise Focused on Results');
    $trustBody = $settings['trust_body_'.$locale] ?? null;
    $ctaTitle = $settings['cta_title_'.$locale] ?? __('Have a Technology Challenge?');
    $ctaSubtitle = $settings['cta_subtitle_'.$locale] ?? __("Let's Talk About It.");
@endphp

<section class="section-padding bg-white">
    <div class="container-public">
        <div class="rounded-3xl border border-brand-border p-8 sm:p-10" data-reveal>
            <h2 class="text-2xl font-bold text-brand-navy">{{ $trustTitle }}</h2>
            @if ($trustBody)
                <p class="mt-3 max-w-3xl text-brand-secondary">{{ $trustBody }}</p>
            @endif
        </div>
    </div>
</section>

<section class="section-padding">
    <div class="container-public">
        <div class="rounded-3xl bg-brand-navy px-8 py-12 text-white" data-reveal>
            <h2 class="text-2xl font-bold sm:text-3xl">{{ $ctaTitle }}</h2>
            <p class="mt-3 max-w-3xl text-white/75">{{ $ctaSubtitle }}</p>
            <a href="{{ route('consultation') }}" class="btn-primary mt-8 inline-flex">{{ __('Book a Consultation') }}</a>
        </div>
    </div>
</section>
@endsection

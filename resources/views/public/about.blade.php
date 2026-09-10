@extends('layouts.public')

@section('title', __('About').' | '.($settings['company_name'] ?? 'QCoreSys'))

@section('content')
@php
    $locale = app()->getLocale();
    $aboutTitle = $settings['about_title_'.$locale] ?? __('About');
    $aboutBody = $settings['about_body_'.$locale] ?? ($settings['company_description_'.$locale] ?? ($settings['company_description'] ?? null));
@endphp

<section class="bg-brand-navy text-white">
    <div class="container-public section-padding !py-16">
        <h1 class="text-3xl font-bold sm:text-4xl">{{ $aboutTitle }}</h1>
        <p class="mt-4 max-w-2xl text-white/75">{{ __('Learn more about our company and expertise.') }}</p>
    </div>
</section>

<section class="section-padding">
    <div class="container-public max-w-3xl">
        @if ($aboutBody)
            <div class="prose max-w-none text-brand-secondary leading-relaxed" data-reveal>
                {!! nl2br(e($aboutBody)) !!}
            </div>
        @endif
    </div>
</section>

@if ($featured->isNotEmpty())
<section class="section-padding bg-white">
    <div class="container-public">
        <h2 class="mb-8 text-2xl font-bold text-brand-navy">{{ __('Featured Services') }}</h2>
        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-4">
            @foreach ($featured as $service)
                @include('public.partials.service-card', ['service' => $service])
            @endforeach
        </div>
    </div>
</section>
@endif
@endsection

@extends('layouts.public')

@section('title', __('About').' | '.($settings['company_name'] ?? 'QCoreSys'))

@section('content')
@php
    $locale = app()->getLocale();
    $aboutTitle = $settings['about_title_'.$locale] ?? __('The QCoreSys Technology Platform');
    $aboutBody = $settings['about_body_'.$locale]
        ?? ($settings['company_description_'.$locale]
        ?? ($settings['company_description']
        ?? __('about.fallback_body')));

    $highlights = [
        __('Engineering team with certified banking and software expertise.'),
        __('Full alignment with financial and technical security standards.'),
        __('Flexible solutions for cloud and on-premise deployment.'),
    ];

    $faqs = [
        [
            'q' => __('about.faq_1_q'),
            'a' => __('about.faq_1_a'),
        ],
        [
            'q' => __('about.faq_2_q'),
            'a' => __('about.faq_2_a'),
        ],
        [
            'q' => __('about.faq_3_q'),
            'a' => __('about.faq_3_a'),
        ],
        [
            'q' => __('about.faq_4_q'),
            'a' => __('about.faq_4_a'),
        ],
    ];
@endphp

<section class="section-padding bg-white">
    <div class="container-public">
        <div class="grid items-start gap-10 lg:grid-cols-12 lg:gap-14">
            <div class="lg:col-span-7" data-reveal>
                <h1 class="text-3xl font-bold leading-tight text-brand-navy sm:text-4xl lg:text-[2.5rem]">
                    {{ $aboutTitle }}
                </h1>

                <p class="mt-5 max-w-2xl text-base leading-relaxed text-brand-secondary sm:text-lg">
                    {{ $aboutBody }}
                </p>

                <ul class="mt-8 space-y-4">
                    @foreach ($highlights as $highlight)
                        <li class="flex items-start gap-3">
                            <span class="mt-0.5 inline-flex h-5 w-5 shrink-0 items-center justify-center rounded bg-brand-cyan/15 text-brand-cyan" aria-hidden="true">
                                <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd"/>
                                </svg>
                            </span>
                            <span class="text-sm font-medium leading-relaxed text-brand-navy sm:text-base">{{ $highlight }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="lg:col-span-5" data-reveal>
                <div class="rounded-3xl border border-brand-border bg-brand-bg p-8 sm:p-10">
                    <h2 class="text-xl font-bold leading-snug text-brand-navy sm:text-2xl">
                        {{ __('Looking for a technology partner for your next project?') }}
                    </h2>
                    <p class="mt-4 text-sm leading-relaxed text-brand-secondary sm:text-base">
                        {{ __('Contact us today to analyze your organization\'s needs and provide the right consultation.') }}
                    </p>
                    <a href="{{ route('consultation') }}" class="btn-primary mt-8 inline-flex w-full sm:w-auto">
                        {{ __('Talk to our experts') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="faqs" class="section-padding !pt-0" x-data="{ open: null }">
    <div class="container-public max-w-3xl">
        <div class="mb-10 text-center" data-reveal>
            <h2 class="text-2xl font-bold text-brand-navy sm:text-3xl">{{ __('Frequently Asked Questions') }}</h2>
            <p class="mt-3 text-brand-secondary">{{ __('about.faq_subtitle') }}</p>
        </div>

        <div class="space-y-3">
            @foreach ($faqs as $index => $faq)
                <div class="rounded-2xl border border-brand-border bg-white" data-reveal>
                    <button
                        type="button"
                        class="flex w-full items-center justify-between gap-4 px-5 py-4 text-start font-medium text-brand-navy"
                        @click="open = open === {{ $index }} ? null : {{ $index }}"
                        :aria-expanded="(open === {{ $index }}).toString()"
                    >
                        <span>{{ $faq['q'] }}</span>
                        <span class="text-brand-cyan" x-text="open === {{ $index }} ? '−' : '+'"></span>
                    </button>
                    <div
                        x-cloak
                        x-show="open === {{ $index }}"
                        x-transition
                        class="border-t border-brand-border px-5 py-4 text-sm leading-relaxed text-brand-secondary"
                    >
                        {{ $faq['a'] }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

@if ($featured->isNotEmpty())
<section class="section-padding bg-white !pt-0">
    <div class="container-public">
        <h2 class="mb-8 text-2xl font-bold text-brand-navy">{{ __('Featured Services') }}</h2>
        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($featured as $service)
                @include('public.partials.service-card', ['service' => $service])
            @endforeach
        </div>
    </div>
</section>
@endif
@endsection

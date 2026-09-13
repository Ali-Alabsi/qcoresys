@extends('layouts.public')

@section('title', __('Solutions').' | '.($settings['company_name'] ?? 'QCoreSys'))

@section('content')
<section class="bg-brand-navy text-white">
    <div class="container-public section-padding !py-16">
        <h1 class="text-3xl font-bold sm:text-4xl">{{ __('Solutions') }}</h1>
    </div>
</section>

<section class="section-padding">
    <div class="container-public space-y-14">
        @forelse ($grouped as $group)
            <div data-reveal>
                <div class="mb-6 flex items-end justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-bold text-brand-navy">{{ $group['category']->localized_name }}</h2>
                        @if ($group['category']->localized_description)
                            <p class="mt-2 text-brand-secondary">{{ $group['category']->localized_description }}</p>
                        @endif
                    </div>
                    <a href="{{ route('services.index', ['category' => $group['category']->slug]) }}" class="text-sm font-semibold text-brand-navy hover:text-brand-cyan">{{ __('View Service') }}</a>
                </div>
                @if ($group['services']->isEmpty())
                    <p class="text-sm text-brand-muted">{{ __('No services are currently available.') }}</p>
                @else
                    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($group['services'] as $service)
                            @include('public.partials.service-card', ['service' => $service])
                        @endforeach
                    </div>
                @endif
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-brand-border bg-white px-6 py-16 text-center">
                <p class="text-brand-secondary">{{ __('No services are currently available.') }}</p>
            </div>
        @endforelse
    </div>
</section>
@endsection

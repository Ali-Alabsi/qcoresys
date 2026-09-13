@extends('layouts.public')

@section('title', $project->localized_title.' | '.($settings['company_name'] ?? 'QCoreSys'))

@section('content')
<section class="bg-brand-navy text-white">
    <div class="container-public section-padding">
        <h1 class="text-3xl font-bold sm:text-4xl">{{ $project->localized_title }}</h1>
        @if ($project->localized_industry)
            <p class="mt-3 text-brand-cyan">{{ $project->localized_industry }}</p>
        @endif
    </div>
</section>

<section class="section-padding">
    <div class="container-public max-w-4xl space-y-8">
        @if ($project->featured_image)
            <img src="{{ asset('storage/'.$project->featured_image) }}" alt="{{ $project->localized_title }}" class="w-full rounded-2xl object-cover" loading="lazy">
        @endif
        <div class="prose max-w-none text-brand-secondary leading-relaxed">
            {!! nl2br(e($project->localized_description ?: $project->localized_short_description)) !!}
        </div>
        @if ($project->technologies->isNotEmpty())
            <div>
                <h2 class="mb-4 text-lg font-semibold text-brand-navy">{{ __('Technologies') }}</h2>
                <div class="flex flex-wrap gap-2">
                    @foreach ($project->technologies as $tech)
                        <span class="badge-tech">{{ $tech->localized_name }}</span>
                    @endforeach
                </div>
            </div>
        @endif
        @if ($project->services->isNotEmpty())
            <div>
                <h2 class="mb-4 text-lg font-semibold text-brand-navy">{{ __('Services') }}</h2>
                <div class="flex flex-wrap gap-2">
                    @foreach ($project->services as $service)
                        <a href="{{ route('services.show', $service->slug) }}" class="badge-tech hover:border-brand-cyan">{{ $service->localized_name }}</a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</section>
@endsection

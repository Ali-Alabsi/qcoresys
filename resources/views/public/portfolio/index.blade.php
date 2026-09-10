@extends('layouts.public')

@section('title', __('Portfolio').' | '.($settings['company_name'] ?? 'QCoreSys'))

@section('content')
<section class="bg-brand-navy text-white">
    <div class="container-public section-padding !py-16">
        <h1 class="text-3xl font-bold sm:text-4xl">{{ __('Our Work') }}</h1>
    </div>
</section>

<section class="section-padding">
    <div class="container-public">
        @if ($projects->isEmpty())
            <div class="rounded-2xl border border-dashed border-brand-border bg-white px-6 py-16 text-center">
                <p class="text-brand-secondary">{{ __('No portfolio projects are available yet.') }}</p>
            </div>
        @else
            <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($projects as $project)
                    <a href="{{ route('portfolio.show', $project->slug) }}" class="card-premium block" data-reveal>
                        @if ($project->featured_image)
                            <img src="{{ asset('storage/'.$project->featured_image) }}" alt="{{ $project->localized_title }}" class="mb-4 h-44 w-full rounded-xl object-cover" loading="lazy">
                        @endif
                        <h2 class="text-lg font-semibold text-brand-navy">{{ $project->localized_title }}</h2>
                        <p class="mt-2 text-sm text-brand-secondary">{{ \Illuminate\Support\Str::limit($project->localized_short_description, 120) }}</p>
                        @if ($project->localized_industry)
                            <span class="badge-tech mt-4">{{ $project->localized_industry }}</span>
                        @endif
                        <span class="mt-4 inline-block text-sm font-semibold text-brand-cyan">{{ __('View Project') }}</span>
                    </a>
                @endforeach
            </div>
            <div class="mt-10">{{ $projects->links() }}</div>
        @endif
    </div>
</section>
@endsection

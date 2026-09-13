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

<article class="card-premium flex h-full flex-col" data-reveal>
    <div class="mb-4 flex items-start justify-between gap-3">
        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-cyan/10 text-brand-cyan">
            @if ($service->icon)
                <span class="text-lg font-bold">{{ strtoupper(substr($service->icon, 0, 2)) }}</span>
            @else
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 3v2.25M14.25 3v2.25M4.5 9.75h15M5.25 6.75h13.5A1.5 1.5 0 0120.25 8.25v11.25a1.5 1.5 0 01-1.5 1.5H5.25a1.5 1.5 0 01-1.5-1.5V8.25a1.5 1.5 0 011.5-1.5z"/></svg>
            @endif
        </div>
        @if ($service->category)
            <span class="badge-tech">{{ $service->category->localized_name }}</span>
        @endif
    </div>

    <h3 class="text-lg font-semibold text-brand-navy">
        <a href="{{ route('services.show', $service->slug) }}" class="hover:text-brand-navy/80">{{ $service->localized_name }}</a>
    </h3>
    <p class="mt-2 flex-1 text-sm leading-relaxed text-brand-secondary">
        {{ \Illuminate\Support\Str::limit($service->localized_short_description ?: $service->localized_description, 140) }}
    </p>

    @if ($service->relationLoaded('technologies') && $service->technologies->isNotEmpty())
        <div class="mt-4 flex flex-wrap gap-2">
            @foreach ($service->technologies->take(4) as $tech)
                <span class="badge-tech">{{ $tech->localized_name }}</span>
            @endforeach
        </div>
    @elseif ($service->relationLoaded('features') && $service->features->isNotEmpty())
        <div class="mt-4 flex flex-wrap gap-2">
            @foreach ($service->features->take(4) as $feature)
                <span class="badge-tech">{{ $feature->localized_title }}</span>
            @endforeach
        </div>
    @endif

    <div class="mt-5 flex items-center justify-between gap-3 border-t border-brand-border pt-4">
        <div class="text-sm">
            <span class="font-medium text-brand-navy">{{ $pricingLabel }}</span>
            @if ($showPrice)
                <span class="ms-1 text-brand-secondary">{{ $service->currency?->symbol }}{{ number_format((float) $service->starting_price, 0) }}</span>
            @endif
        </div>
    </div>

    <div class="mt-4 flex flex-wrap gap-2">
        <a href="{{ route('services.show', $service->slug) }}" class="btn-secondary !px-4 !py-2 text-xs">{{ __('View Service') }}</a>
        <a href="{{ route('services.request', $service->slug) }}" class="btn-primary !px-4 !py-2 text-xs">{{ __('Request Service') }}</a>
    </div>
</article>

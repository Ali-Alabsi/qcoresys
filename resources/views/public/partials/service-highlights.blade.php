@php
    $highlights = [
        [
            'title' => __('Core Banking Systems'),
            'description' => __('Production-ready core banking systems'),
            'category' => 'core-banking',
        ],
        [
            'title' => __('Oracle Databases'),
            'description' => __('Database engineering & Oracle APEX apps'),
            'category' => 'database-apex',
        ],
        [
            'title' => __('API Gateways'),
            'description' => __('Secure financial system connectivity'),
            'category' => 'api-integration',
        ],
    ];
@endphp

<section class="relative z-10 pt-6 pb-2 sm:pt-8 sm:pb-4" aria-label="{{ __('Featured Services') }}">
    <div class="container-public">
        <div class="grid grid-cols-3 gap-3 rounded-2xl border border-brand-border bg-white p-3 shadow-[0_16px_40px_rgba(9,26,47,0.08)] sm:gap-4 sm:p-4">
            @foreach ($highlights as $item)
                <a href="{{ route('services.index', ['category' => $item['category']]) }}"
                   class="group rounded-xl border border-brand-border bg-white px-5 py-6 text-center transition hover:border-brand-cyan/40 hover:shadow-[0_8px_24px_rgba(9,26,47,0.06)] sm:px-6 sm:py-7">
                    <p class="text-xl font-bold tracking-tight text-brand-cyan sm:text-2xl">{{ $item['title'] }}</p>
                    <p class="mt-2 text-sm leading-relaxed text-brand-text">{{ $item['description'] }}</p>
                </a>
            @endforeach
        </div>
    </div>
</section>

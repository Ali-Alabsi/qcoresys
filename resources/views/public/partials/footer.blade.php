@php
    $locale = app()->getLocale();
    $companyName = $settings['company_name'] ?? 'QCoreSys';
    $description = $settings['company_description_'.$locale] ?? ($settings['company_description'] ?? null);
    $email = $settings['company_email'] ?? null;
    $phone = $settings['company_phone'] ?? null;
    $address = $settings['company_address_'.$locale] ?? ($settings['company_address'] ?? null);
    $socials = collect([
        'linkedin' => $settings['social_linkedin'] ?? null,
        'twitter' => $settings['social_twitter'] ?? null,
        'facebook' => $settings['social_facebook'] ?? null,
        'instagram' => $settings['social_instagram'] ?? null,
        'youtube' => $settings['social_youtube'] ?? null,
    ])->filter();
@endphp

<footer class="mt-auto border-t border-brand-border bg-brand-navy text-white">
    <div class="container-public section-padding !py-14">
        <div class="grid gap-10 md:grid-cols-2 lg:grid-cols-4">
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    @include('public.partials.logo', [
                        'settings' => $settings,
                        'companyName' => $companyName,
                        'heightClass' => 'h-10',
                        'showName' => true,
                        'nameClass' => 'text-lg font-semibold text-white',
                    ])
                </div>
                @if ($description)
                    <p class="text-sm leading-relaxed text-white/70">{{ $description }}</p>
                @endif
                @if ($socials->isNotEmpty())
                    <div class="flex flex-wrap gap-3 pt-2">
                        @foreach ($socials as $network => $url)
                            <a href="{{ $url }}" class="rounded-lg border border-white/15 px-3 py-1.5 text-xs text-white/80 transition hover:border-brand-cyan hover:text-brand-cyan" target="_blank" rel="noopener noreferrer">{{ ucfirst($network) }}</a>
                        @endforeach
                    </div>
                @endif
            </div>

            <div>
                <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-brand-cyan">{{ __('Services') }}</h3>
                <ul class="space-y-2 text-sm text-white/75">
                    <li><a class="hover:text-white" href="{{ route('services.index') }}">{{ __('Services') }}</a></li>
                    <li><a class="hover:text-white" href="{{ route('solutions') }}">{{ __('Solutions') }}</a></li>
                    <li><a class="hover:text-white" href="{{ route('consultation') }}">{{ __('Request Consultation') }}</a></li>
                </ul>
            </div>

            <div>
                <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-brand-cyan">{{ __('Company') }}</h3>
                <ul class="space-y-2 text-sm text-white/75">
                    <li><a class="hover:text-white" href="{{ route('about') }}">{{ __('About') }}</a></li>
                    <li><a class="hover:text-white" href="{{ route('contact') }}">{{ __('Contact') }}</a></li>
                </ul>
            </div>

            <div>
                <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-brand-cyan">{{ __('Contact') }}</h3>
                <ul class="space-y-2 text-sm text-white/75">
                    @if ($email)
                        <li><a class="hover:text-white" href="mailto:{{ $email }}">{{ $email }}</a></li>
                    @endif
                    @if ($phone)
                        <li><a class="hover:text-white" href="tel:{{ preg_replace('/\s+/', '', $phone) }}">{{ $phone }}</a></li>
                    @endif
                    @if ($address)
                        <li>{{ $address }}</li>
                    @endif
                </ul>
                <div class="mt-4 flex items-center gap-2 text-xs">
                    <a href="{{ route('locale.switch', 'ar') }}" class="{{ $locale === 'ar' ? 'text-brand-cyan' : 'text-white/60' }}">AR</a>
                    <span class="text-white/30">|</span>
                    <a href="{{ route('locale.switch', 'en') }}" class="{{ $locale === 'en' ? 'text-brand-cyan' : 'text-white/60' }}">EN</a>
                </div>
            </div>
        </div>

        <div class="mt-12 border-t border-white/10 pt-6 text-sm text-white/50">
            &copy; {{ date('Y') }} {{ $companyName }}. {{ __('All rights reserved.') }}
        </div>
    </div>
</footer>

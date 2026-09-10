@extends('layouts.public')

@section('title', __('Contact').' | '.($settings['company_name'] ?? 'QCoreSys'))

@section('content')
@php
    $locale = app()->getLocale();
    $email = $settings['company_email'] ?? null;
    $phone = $settings['company_phone'] ?? null;
    $address = $settings['company_address_'.$locale] ?? ($settings['company_address'] ?? null);
@endphp

<section class="bg-brand-navy text-white">
    <div class="container-public section-padding !py-16">
        <h1 class="text-3xl font-bold sm:text-4xl">{{ __('Contact') }}</h1>
        <p class="mt-4 max-w-2xl text-white/75">{{ __('Get in touch with our team.') }}</p>
    </div>
</section>

<section class="section-padding">
    <div class="container-public grid gap-10 lg:grid-cols-12">
        <div class="lg:col-span-4 space-y-4">
            @if ($email)
                <div class="card-premium !p-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-brand-muted">{{ __('Email') }}</p>
                    <a href="mailto:{{ $email }}" class="mt-2 block font-medium text-brand-navy">{{ $email }}</a>
                </div>
            @endif
            @if ($phone)
                <div class="card-premium !p-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-brand-muted">{{ __('Phone') }}</p>
                    <a href="tel:{{ preg_replace('/\s+/', '', $phone) }}" class="mt-2 block font-medium text-brand-navy">{{ $phone }}</a>
                </div>
            @endif
            @if ($address)
                <div class="card-premium !p-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-brand-muted">{{ __('Contact') }}</p>
                    <p class="mt-2 text-brand-navy">{{ $address }}</p>
                </div>
            @endif
        </div>

        <div class="lg:col-span-8">
            <form method="POST" action="{{ route('contact.store') }}" class="card-premium space-y-4">
                @csrf
                <input type="text" name="website" class="hidden" tabindex="-1" autocomplete="off" aria-hidden="true">
                <div>
                    <label class="label-public" for="full_name">{{ __('Full Name') }}</label>
                    <input id="full_name" name="full_name" type="text" required value="{{ old('full_name') }}" class="input-public">
                    @error('full_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="label-public" for="company_name">{{ __('Company Name') }}</label>
                    <input id="company_name" name="company_name" type="text" value="{{ old('company_name') }}" class="input-public">
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="label-public" for="email">{{ __('Email') }}</label>
                        <input id="email" name="email" type="email" required value="{{ old('email') }}" class="input-public">
                        @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="label-public" for="phone">{{ __('Phone') }}</label>
                        <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" class="input-public">
                    </div>
                </div>
                <div>
                    <label class="label-public" for="subject">{{ __('Subject') }}</label>
                    <input id="subject" name="subject" type="text" value="{{ old('subject') }}" class="input-public">
                </div>
                <div>
                    <label class="label-public" for="message">{{ __('Message') }}</label>
                    <textarea id="message" name="message" rows="5" required class="input-public">{{ old('message') }}</textarea>
                    @error('message')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <button type="submit" class="btn-primary">{{ __('Send Message') }}</button>
            </form>
        </div>
    </div>
</section>
@endsection

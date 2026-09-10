@extends('layouts.public')

@section('title', __('Request Number').' | '.($settings['company_name'] ?? 'QCoreSys'))

@section('content')
<section class="section-padding">
    <div class="container-public max-w-xl text-center">
        <div class="card-premium !p-10">
            <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-brand-cyan/15 text-brand-cyan">
                <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.5 12.75l6 6 9-13.5"/></svg>
            </div>
            <h1 class="text-2xl font-bold text-brand-navy">{{ $successMessage ?? __('Your request has been submitted successfully.') }}</h1>
            <p class="mt-4 text-brand-secondary">{{ __('Thank you for contacting us. Our team will review your request and get back to you shortly.') }}</p>
            <div class="mt-8 rounded-xl bg-brand-bg px-4 py-5">
                <p class="text-sm text-brand-secondary">{{ __('Request Number') }}</p>
                <p class="mt-1 text-2xl font-bold tracking-wide text-brand-navy">{{ $requestNumber }}</p>
            </div>
            <a href="{{ route('services.index') }}" class="btn-primary mt-8 inline-flex">{{ __('Back to Services') }}</a>
        </div>
    </div>
</section>
@endsection

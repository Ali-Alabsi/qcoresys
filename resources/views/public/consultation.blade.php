@extends('layouts.public')

@section('title', __('Request Consultation').' | '.($settings['company_name'] ?? 'QCoreSys'))

@section('content')
<section class="section-padding">
    <div class="container-public max-w-2xl">
        <h1 class="text-3xl font-bold text-brand-navy">{{ __('Request a Consultation') }}</h1>
        <p class="mt-3 text-brand-secondary">{{ __('Tell us what you need and our team will contact you to discuss your requirements.') }}</p>

        <form method="POST" action="{{ route('consultation.store') }}" class="card-premium mt-8 space-y-4">
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
                    <input id="phone" name="phone" type="tel" required value="{{ old('phone') }}" class="input-public">
                    @error('phone')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>
            <div>
                <label class="label-public" for="service_id">{{ __('Service') }}</label>
                <select id="service_id" name="service_id" class="input-public">
                    <option value="">{{ __('Select an option') }}</option>
                    @foreach ($services as $service)
                        <option value="{{ $service->id }}" @selected((string) old('service_id') === (string) $service->id)>{{ $service->localized_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label-public" for="preferred_contact_method">{{ __('Preferred Contact Method') }}</label>
                <select id="preferred_contact_method" name="preferred_contact_method" class="input-public">
                    <option value="">{{ __('Select an option') }}</option>
                    @foreach ($options['contact_methods'] as $option)
                        <option value="{{ $option->code }}" @selected(old('preferred_contact_method') === $option->code)>{{ $option->localized_label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label-public" for="description">{{ __('Project Description') }}</label>
                <textarea id="description" name="description" rows="5" required class="input-public">{{ old('description') }}</textarea>
                @error('description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <button type="submit" class="btn-primary">{{ __('Book a Consultation') }}</button>
        </form>
    </div>
</section>
@endsection

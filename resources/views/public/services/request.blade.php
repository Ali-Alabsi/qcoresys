@extends('layouts.public')

@section('title', __('Service Request').' | '.$service->localized_name)

@section('content')
<div class="section-padding" x-data="serviceRequestWizard()">
    <div class="container-public max-w-3xl">
        <div class="mb-8">
            <p class="text-sm font-semibold text-brand-cyan">{{ $service->localized_name }}</p>
            <h1 class="mt-2 text-3xl font-bold text-brand-navy">{{ __('Service Request') }}</h1>
        </div>

        <div class="mb-8 flex items-center gap-2" role="list" aria-label="Progress">
            <template x-for="step in 4" :key="step">
                <div class="flex items-center gap-2" role="listitem">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full text-xs font-bold"
                         :class="currentStep >= step ? 'bg-brand-cyan text-brand-navy' : 'bg-brand-border text-brand-secondary'"
                         x-text="step"></div>
                    <span class="hidden text-xs text-brand-secondary sm:inline" x-text="stepLabels[step-1]"></span>
                    <span class="h-px w-6 bg-brand-border last:hidden" x-show="step < 4"></span>
                </div>
            </template>
        </div>

        <form method="POST" action="{{ route('services.request.store', $service->slug) }}" enctype="multipart/form-data" class="card-premium space-y-6" @submit="if (website) $event.preventDefault()">
            @csrf
            <input type="hidden" name="service_id" value="{{ $service->id }}">
            <input type="text" name="website" x-model="website" class="hidden" tabindex="-1" autocomplete="off" aria-hidden="true">

            <div x-show="currentStep === 1" class="space-y-4">
                <h2 class="text-lg font-semibold text-brand-navy">{{ __('Your Information') }}</h2>
                <div>
                    <label class="label-public" for="full_name">{{ __('Full Name') }}</label>
                    <input id="full_name" name="full_name" type="text" required value="{{ old('full_name') }}" class="input-public" x-model="form.full_name">
                    @error('full_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="label-public" for="company_name">{{ __('Company Name') }} <span class="text-brand-muted">({{ __('Optional') }})</span></label>
                    <input id="company_name" name="company_name" type="text" value="{{ old('company_name') }}" class="input-public" x-model="form.company_name">
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="label-public" for="email">{{ __('Email') }}</label>
                        <input id="email" name="email" type="email" required value="{{ old('email') }}" class="input-public" x-model="form.email">
                        @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="label-public" for="phone">{{ __('Phone') }}</label>
                        <input id="phone" name="phone" type="tel" required value="{{ old('phone') }}" class="input-public" x-model="form.phone">
                        @error('phone')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <div x-cloak x-show="currentStep === 2" class="space-y-4">
                <h2 class="text-lg font-semibold text-brand-navy">{{ __('Project Requirements') }}</h2>
                <div>
                    <label class="label-public" for="project_type">{{ __('Project Type') }}</label>
                    <select id="project_type" name="project_type" class="input-public" x-model="form.project_type">
                        <option value="">{{ __('Select an option') }}</option>
                        @foreach ($options['project_types'] as $option)
                            <option value="{{ $option->code }}" @selected(old('project_type') === $option->code)>{{ $option->localized_label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label-public" for="description">{{ __('Project Description') }}</label>
                    <textarea id="description" name="description" rows="5" required class="input-public" x-model="form.description">{{ old('description') }}</textarea>
                    @error('description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="label-public" for="requirements">{{ __('Requirements') }}</label>
                    <textarea id="requirements" name="requirements" rows="4" class="input-public" x-model="form.requirements">{{ old('requirements') }}</textarea>
                </div>
                <div>
                    <label class="label-public" for="attachments">{{ __('Attachments') }}</label>
                    <input id="attachments" name="attachments[]" type="file" multiple accept=".pdf,.doc,.docx,.png,.jpg,.jpeg,.zip" class="input-public">
                </div>
            </div>

            <div x-cloak x-show="currentStep === 3" class="space-y-4">
                <h2 class="text-lg font-semibold text-brand-navy">{{ __('Budget & Timeline') }}</h2>
                <div>
                    <label class="label-public" for="budget_range">{{ __('Budget Range') }}</label>
                    <select id="budget_range" name="budget_range" class="input-public" x-model="form.budget_range">
                        <option value="">{{ __('Select an option') }}</option>
                        @foreach ($options['budget_ranges'] as $option)
                            <option value="{{ $option->code }}" @selected(old('budget_range') === $option->code)>{{ $option->localized_label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label-public" for="timeline">{{ __('Expected Timeline') }}</label>
                    <select id="timeline" name="timeline" class="input-public" x-model="form.timeline">
                        <option value="">{{ __('Select an option') }}</option>
                        @foreach ($options['timelines'] as $option)
                            <option value="{{ $option->code }}" @selected(old('timeline') === $option->code)>{{ $option->localized_label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label-public" for="preferred_contact_method">{{ __('Preferred Contact Method') }}</label>
                    <select id="preferred_contact_method" name="preferred_contact_method" class="input-public" x-model="form.preferred_contact_method">
                        <option value="">{{ __('Select an option') }}</option>
                        @foreach ($options['contact_methods'] as $option)
                            <option value="{{ $option->code }}" @selected(old('preferred_contact_method') === $option->code)>{{ $option->localized_label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label-public" for="additional_notes">{{ __('Additional Notes') }}</label>
                    <textarea id="additional_notes" name="additional_notes" rows="3" class="input-public" x-model="form.additional_notes">{{ old('additional_notes') }}</textarea>
                </div>
            </div>

            <div x-cloak x-show="currentStep === 4" class="space-y-4">
                <h2 class="text-lg font-semibold text-brand-navy">{{ __('Review & Submit') }}</h2>
                <div class="rounded-xl bg-brand-bg p-4 text-sm text-brand-secondary space-y-2">
                    <p><strong class="text-brand-navy">{{ __('Service') }}:</strong> {{ $service->localized_name }}</p>
                    <p><strong class="text-brand-navy">{{ __('Full Name') }}:</strong> <span x-text="form.full_name"></span></p>
                    <p><strong class="text-brand-navy">{{ __('Email') }}:</strong> <span x-text="form.email"></span></p>
                    <p><strong class="text-brand-navy">{{ __('Phone') }}:</strong> <span x-text="form.phone"></span></p>
                    <p><strong class="text-brand-navy">{{ __('Project Description') }}:</strong> <span x-text="form.description"></span></p>
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-brand-border pt-6">
                <button type="button" class="btn-secondary" x-show="currentStep > 1" @click="currentStep--">{{ __('Back') }}</button>
                <div class="ms-auto flex gap-3">
                    <button type="button" class="btn-navy" x-show="currentStep < 4" @click="nextStep()">{{ __('Continue') }}</button>
                    <button type="submit" class="btn-primary" x-show="currentStep === 4">{{ __('Submit Request') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>

@php
    $stepLabels = [
        __('Your Information'),
        __('Project Requirements'),
        __('Budget & Timeline'),
        __('Review & Submit'),
    ];
@endphp

@push('scripts')
<script>
function serviceRequestWizard() {
    return {
        currentStep: 1,
        website: '',
        stepLabels: @json($stepLabels),
        form: {
            full_name: @json(old('full_name', '')),
            company_name: @json(old('company_name', '')),
            email: @json(old('email', '')),
            phone: @json(old('phone', '')),
            project_type: @json(old('project_type', '')),
            description: @json(old('description', '')),
            requirements: @json(old('requirements', '')),
            budget_range: @json(old('budget_range', '')),
            timeline: @json(old('timeline', '')),
            preferred_contact_method: @json(old('preferred_contact_method', '')),
            additional_notes: @json(old('additional_notes', '')),
        },
        nextStep() {
            if (this.currentStep === 1 && (!this.form.full_name || !this.form.email || !this.form.phone)) {
                return;
            }
            if (this.currentStep === 2 && !this.form.description) {
                return;
            }
            if (this.currentStep < 4) this.currentStep++;
        }
    }
}
</script>
@endpush
@endsection

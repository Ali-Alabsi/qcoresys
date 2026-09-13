@extends('layouts.admin')
@section('title', __('Edit service'))
@section('content')
@php
    $featureRows = old('features', $service->features->map(fn ($f) => [
        'title' => $f->title, 'title_ar' => $f->title_ar, 'description' => $f->description, 'description_ar' => $f->description_ar,
    ])->values()->all());
    $benefitRows = old('benefits', $service->benefits->map(fn ($b) => [
        'title' => $b->title, 'title_ar' => $b->title_ar, 'description' => $b->description, 'description_ar' => $b->description_ar,
    ])->values()->all());
    $stepRows = old('steps', $service->processSteps->map(fn ($s) => [
        'title' => $s->title, 'title_ar' => $s->title_ar, 'description' => $s->description, 'description_ar' => $s->description_ar,
    ])->values()->all());
@endphp
<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h1 class="text-2xl font-bold">{{ __('Edit service') }}</h1>
        <p class="text-sm text-slate-500">{{ $service->service_code }} · {{ $service->localized('name') }}</p>
    </div>
    <a href="{{ route('admin.services.index') }}" class="btn-secondary !px-4 !py-2">{{ __('Back') }}</a>
</div>

<form method="POST" action="{{ route('admin.services.update', $service) }}" class="space-y-6"
      x-data="{
        features: {{ \Illuminate\Support\Js::from($featureRows ?: [['title'=>'','title_ar'=>'','description'=>'','description_ar'=>'']]) }},
        benefits: {{ \Illuminate\Support\Js::from($benefitRows ?: [['title'=>'','title_ar'=>'','description'=>'','description_ar'=>'']]) }},
        steps: {{ \Illuminate\Support\Js::from($stepRows ?: [['title'=>'','title_ar'=>'','description'=>'','description_ar'=>'']]) }},
        blank(){ return {title:'',title_ar:'',description:'',description_ar:''}; }
      }">
    @csrf @method('PUT')

    <section class="rounded-2xl border border-slate-200 bg-white p-6">
        <h2 class="mb-4 text-lg font-semibold text-brand-navy">{{ __('Basic information') }}</h2>
        <div class="grid gap-5 sm:grid-cols-2">
            <div><label class="label-public">{{ __('Category') }}</label>
                <select name="category_id" class="input-public" required>
                    @foreach($categories as $id => $label)<option value="{{ $id }}" @selected((string)old('category_id', $service->category_id)===(string)$id)>{{ $label }}</option>@endforeach
                </select>
            </div>
            <div><label class="label-public">{{ __('Code') }}</label><input class="input-public" name="service_code" value="{{ old('service_code', $service->service_code) }}" required></div>
            <div><label class="label-public">{{ __('Slug') }}</label><input class="input-public" name="slug" value="{{ old('slug', $service->slug) }}" required></div>
            <div><label class="label-public">{{ __('Icon') }}</label><input class="input-public" name="icon" value="{{ old('icon', $service->icon) }}"></div>
            <div><label class="label-public">{{ __('English name') }}</label><input class="input-public" name="name" value="{{ old('name', $service->name) }}" required></div>
            <div><label class="label-public">{{ __('Arabic name') }}</label><input class="input-public" name="name_ar" value="{{ old('name_ar', $service->name_ar) }}"></div>
            <div class="sm:col-span-2"><label class="label-public">{{ __('English summary') }}</label><textarea class="input-public" name="short_description" rows="2">{{ old('short_description', $service->short_description) }}</textarea></div>
            <div class="sm:col-span-2"><label class="label-public">{{ __('Arabic summary') }}</label><textarea class="input-public" name="short_description_ar" rows="2">{{ old('short_description_ar', $service->short_description_ar) }}</textarea></div>
        </div>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-6">
        <h2 class="mb-4 text-lg font-semibold text-brand-navy">{{ __('Page content') }}</h2>
        <div class="grid gap-5 sm:grid-cols-2">
            <div class="sm:col-span-2"><label class="label-public">{{ __('Overview (EN)') }}</label><textarea class="input-public" name="overview" rows="4">{{ old('overview', $service->overview) }}</textarea></div>
            <div class="sm:col-span-2"><label class="label-public">{{ __('Overview (AR)') }}</label><textarea class="input-public" name="overview_ar" rows="4">{{ old('overview_ar', $service->overview_ar) }}</textarea></div>
            <div class="sm:col-span-2"><label class="label-public">{{ __('Description (EN)') }}</label><textarea class="input-public" name="description" rows="5">{{ old('description', $service->description) }}</textarea></div>
            <div class="sm:col-span-2"><label class="label-public">{{ __('Description (AR)') }}</label><textarea class="input-public" name="description_ar" rows="5">{{ old('description_ar', $service->description_ar) }}</textarea></div>
        </div>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-6">
        <h2 class="mb-4 text-lg font-semibold text-brand-navy">{{ __('Visibility and pricing') }}</h2>
        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            <div><label class="label-public">{{ __('Service type') }}</label>
                <select name="service_type" class="input-public">@foreach($serviceTypes as $value=>$label)<option value="{{ $value }}" @selected((string)old('service_type', $service->service_type?->value)===(string)$value)>{{ $label }}</option>@endforeach</select>
            </div>
            <div><label class="label-public">{{ __('Billing type') }}</label>
                <select name="billing_type" class="input-public">@foreach($billingTypes as $value=>$label)<option value="{{ $value }}" @selected((string)old('billing_type', $service->billing_type?->value)===(string)$value)>{{ $label }}</option>@endforeach</select>
            </div>
            <div><label class="label-public">{{ __('Pricing type') }}</label>
                <select name="pricing_type" class="input-public">@foreach($pricingTypes as $value=>$label)<option value="{{ $value }}" @selected((string)old('pricing_type', $service->pricing_type?->value)===(string)$value)>{{ $label }}</option>@endforeach</select>
            </div>
            <div><label class="label-public">{{ __('Currency') }}</label>
                <select name="currency_id" class="input-public">@foreach($currencies as $id=>$code)<option value="{{ $id }}" @selected((string)old('currency_id', $service->currency_id)===(string)$id)>{{ $code }}</option>@endforeach</select>
            </div>
            <div><label class="label-public">{{ __('Starting price') }}</label><input class="input-public" type="number" step="0.01" name="starting_price" value="{{ old('starting_price', $service->starting_price) }}"></div>
            <div><label class="label-public">{{ __('Default price') }}</label><input class="input-public" type="number" step="0.01" name="default_price" value="{{ old('default_price', $service->default_price) }}"></div>
            <div><label class="label-public">{{ __('Tax rate') }}</label><input class="input-public" type="number" step="0.01" name="tax_rate" value="{{ old('tax_rate', $service->tax_rate) }}"></div>
            <div><label class="label-public">{{ __('Sort order') }}</label><input class="input-public" type="number" name="sort_order" value="{{ old('sort_order', $service->sort_order) }}" required></div>
            <div class="flex flex-col justify-end gap-2 pb-2 text-sm">
                <label class="flex items-center gap-2"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $service->is_active))> {{ __('Active') }}</label>
                <label class="flex items-center gap-2"><input type="checkbox" name="is_public" value="1" @checked(old('is_public', $service->is_public))> {{ __('Public') }}</label>
                <label class="flex items-center gap-2"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $service->is_featured))> {{ __('Featured') }}</label>
            </div>
        </div>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-6">
        <h2 class="mb-4 text-lg font-semibold text-brand-navy">{{ __('SEO') }}</h2>
        <div class="grid gap-5 sm:grid-cols-2">
            <div><label class="label-public">{{ __('Meta title (EN)') }}</label><input class="input-public" name="meta_title" value="{{ old('meta_title', $service->meta_title) }}"></div>
            <div><label class="label-public">{{ __('Meta title (AR)') }}</label><input class="input-public" name="meta_title_ar" value="{{ old('meta_title_ar', $service->meta_title_ar) }}"></div>
            <div class="sm:col-span-2"><label class="label-public">{{ __('Meta description (EN)') }}</label><textarea class="input-public" name="meta_description" rows="2">{{ old('meta_description', $service->meta_description) }}</textarea></div>
            <div class="sm:col-span-2"><label class="label-public">{{ __('Meta description (AR)') }}</label><textarea class="input-public" name="meta_description_ar" rows="2">{{ old('meta_description_ar', $service->meta_description_ar) }}</textarea></div>
        </div>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-6">
        <h2 class="mb-4 text-lg font-semibold text-brand-navy">{{ __('Technologies') }}</h2>
        <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($technologies as $id => $label)
                <label class="flex items-center gap-2 rounded-xl border border-slate-200 px-3 py-2 text-sm">
                    <input type="checkbox" name="technology_ids[]" value="{{ $id }}" @checked(in_array($id, old('technology_ids', $selectedTechnologyIds), false))>
                    {{ $label }}
                </label>
            @endforeach
        </div>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-6">
        <div class="mb-4 flex items-center justify-between"><h2 class="text-lg font-semibold text-brand-navy">{{ __('Features') }}</h2>
            <button type="button" class="btn-secondary !px-3 !py-1.5" @click="features.push(blank())">{{ __('Add feature') }}</button></div>
        <template x-for="(row, index) in features" :key="'f'+index">
            <div class="mb-4 grid gap-3 rounded-xl border border-slate-100 bg-slate-50 p-4 sm:grid-cols-2">
                <input class="input-public" :name="`features[${index}][title]`" x-model="row.title" placeholder="{{ __('English title') }}">
                <input class="input-public" :name="`features[${index}][title_ar]`" x-model="row.title_ar" placeholder="{{ __('Arabic title') }}">
                <textarea class="input-public sm:col-span-2" rows="2" :name="`features[${index}][description]`" x-model="row.description" placeholder="{{ __('English summary') }}"></textarea>
                <textarea class="input-public sm:col-span-2" rows="2" :name="`features[${index}][description_ar]`" x-model="row.description_ar" placeholder="{{ __('Arabic summary') }}"></textarea>
                <button type="button" class="text-sm font-semibold text-red-600" @click="features.splice(index,1)">{{ __('Remove') }}</button>
            </div>
        </template>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-6">
        <div class="mb-4 flex items-center justify-between"><h2 class="text-lg font-semibold text-brand-navy">{{ __('Benefits') }}</h2>
            <button type="button" class="btn-secondary !px-3 !py-1.5" @click="benefits.push(blank())">{{ __('Add benefit') }}</button></div>
        <template x-for="(row, index) in benefits" :key="'b'+index">
            <div class="mb-4 grid gap-3 rounded-xl border border-slate-100 bg-slate-50 p-4 sm:grid-cols-2">
                <input class="input-public" :name="`benefits[${index}][title]`" x-model="row.title" placeholder="{{ __('English title') }}">
                <input class="input-public" :name="`benefits[${index}][title_ar]`" x-model="row.title_ar" placeholder="{{ __('Arabic title') }}">
                <textarea class="input-public sm:col-span-2" rows="2" :name="`benefits[${index}][description]`" x-model="row.description"></textarea>
                <textarea class="input-public sm:col-span-2" rows="2" :name="`benefits[${index}][description_ar]`" x-model="row.description_ar"></textarea>
                <button type="button" class="text-sm font-semibold text-red-600" @click="benefits.splice(index,1)">{{ __('Remove') }}</button>
            </div>
        </template>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-6">
        <div class="mb-4 flex items-center justify-between"><h2 class="text-lg font-semibold text-brand-navy">{{ __('Process steps') }}</h2>
            <button type="button" class="btn-secondary !px-3 !py-1.5" @click="steps.push(blank())">{{ __('Add step') }}</button></div>
        <template x-for="(row, index) in steps" :key="'s'+index">
            <div class="mb-4 grid gap-3 rounded-xl border border-slate-100 bg-slate-50 p-4 sm:grid-cols-2">
                <input class="input-public" :name="`steps[${index}][title]`" x-model="row.title" placeholder="{{ __('English title') }}">
                <input class="input-public" :name="`steps[${index}][title_ar]`" x-model="row.title_ar" placeholder="{{ __('Arabic title') }}">
                <textarea class="input-public sm:col-span-2" rows="2" :name="`steps[${index}][description]`" x-model="row.description"></textarea>
                <textarea class="input-public sm:col-span-2" rows="2" :name="`steps[${index}][description_ar]`" x-model="row.description_ar"></textarea>
                <button type="button" class="text-sm font-semibold text-red-600" @click="steps.splice(index,1)">{{ __('Remove') }}</button>
            </div>
        </template>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-6">
        <h2 class="mb-4 text-lg font-semibold text-brand-navy">{{ __('FAQs') }}</h2>
        <div class="grid gap-2 sm:grid-cols-1">
            @forelse($faqs as $id => $label)
                <label class="flex items-start gap-2 rounded-xl border border-slate-200 px-3 py-2 text-sm">
                    <input class="mt-1" type="checkbox" name="faq_ids[]" value="{{ $id }}" @checked(in_array($id, old('faq_ids', $selectedFaqIds), false))>
                    <span>{{ $label }}</span>
                </label>
            @empty
                <p class="text-sm text-slate-500">{{ __('No FAQs available.') }}</p>
            @endforelse
        </div>
    </section>

    <div class="flex gap-3">
        <button class="btn-primary">{{ __('Save service') }}</button>
        <a href="{{ route('admin.services.index') }}" class="btn-secondary !px-4 !py-2">{{ __('Cancel') }}</a>
    </div>
</form>
@endsection

@extends('layouts.admin')
@section('title', __('Public company settings'))
@section('content')
<h1 class="mb-6 text-2xl font-bold">{{ __('Public company settings') }}</h1>
<form method="POST" action="{{ route('admin.settings.update') }}" class="max-w-4xl rounded-2xl border border-slate-200 bg-white p-6">
    @csrf @method('PUT')
    <div class="grid gap-5 sm:grid-cols-2">
        @foreach($settings as $setting)
            @php
                $labelKey = 'settings.'.$setting->key;
                $label = __($labelKey);
                if ($label === $labelKey) {
                    $label = $setting->description ?: \Illuminate\Support\Str::headline(str_replace('_', ' ', $setting->key));
                }
                $groupKey = 'settings_group.'.$setting->group;
                $group = __($groupKey);
                if ($group === $groupKey) {
                    $group = \Illuminate\Support\Str::headline((string) $setting->group);
                }
            @endphp
            <div class="{{ strlen((string) $setting->value) > 100 ? 'sm:col-span-2' : '' }}">
                <div class="mb-1 flex items-center justify-between gap-2">
                    <label class="label-public !mb-0">{{ $label }}</label>
                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold uppercase text-slate-500">{{ $group }}</span>
                </div>
                @if(strlen((string) $setting->value) > 100)
                    <textarea class="input-public" name="settings[{{ $setting->id }}]" rows="4">{{ old('settings.'.$setting->id, $setting->value) }}</textarea>
                @else
                    <input class="input-public" name="settings[{{ $setting->id }}]" value="{{ old('settings.'.$setting->id, $setting->value) }}">
                @endif
            </div>
        @endforeach
    </div>
    <button class="btn-primary mt-6">{{ __('Save settings') }}</button>
</form>
@endsection

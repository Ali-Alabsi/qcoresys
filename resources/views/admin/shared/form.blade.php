@extends('layouts.admin')
@section('title', $title)
@section('content')
<h1 class="mb-6 text-2xl font-bold">{{ $title }}</h1>
<form method="POST" action="{{ $action }}" class="max-w-3xl rounded-2xl border border-slate-200 bg-white p-6">
    @csrf
    @if($method !== 'POST') @method($method) @endif
    <div class="grid gap-5 sm:grid-cols-2">
        @foreach($fields as $name => $field)
            @php
                $type = $field['type'] ?? 'text';
                $raw = old($name, $record ? data_get($record, $name) : ($field['value'] ?? ''));
                $value = $raw instanceof \BackedEnum ? $raw->value : $raw;
                if ($type === 'date' && $value instanceof \DateTimeInterface) $value = $value->format('Y-m-d');
            @endphp
            <div class="{{ $type === 'textarea' ? 'sm:col-span-2' : '' }}">
                @if($type === 'checkbox')
                    <input type="hidden" name="{{ $name }}" value="0">
                    <label class="flex items-center gap-2 pt-7 text-sm font-medium"><input type="checkbox" name="{{ $name }}" value="1" @checked((bool)$value)> {{ $field['label'] }}</label>
                @else
                    <label class="label-public" for="{{ $name }}">{{ $field['label'] }}</label>
                    @if($type === 'select')
                        <select class="input-public" id="{{ $name }}" name="{{ $name }}" @if(($field['required'] ?? true) !== false) required @endif>
                            <option value="">{{ __('Select') }}</option>
                            @foreach($field['options'] as $optionValue => $optionLabel)<option value="{{ $optionValue }}" @selected((string)$value === (string)$optionValue)>{{ $optionLabel }}</option>@endforeach
                        </select>
                    @elseif($type === 'textarea')
                        <textarea class="input-public" id="{{ $name }}" name="{{ $name }}" rows="4">{{ $value }}</textarea>
                    @else
                        <input class="input-public" id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ $value }}" step="{{ $field['step'] ?? '' }}">
                    @endif
                    @error($name)<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                @endif
            </div>
        @endforeach
    </div>
    <div class="mt-6 flex gap-3"><button class="btn-primary">{{ __('Save') }}</button><button type="button" class="btn-secondary" onclick="history.back()">{{ __('Cancel') }}</button></div>
</form>
@endsection

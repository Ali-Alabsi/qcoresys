@extends('layouts.admin')
@section('title', $title)
@section('content')
<div class="mb-6 flex items-center justify-between"><h1 class="text-2xl font-bold">{{ $title }}</h1><button class="btn-secondary !px-4 !py-2" onclick="history.back()">{{ __('Back') }}</button></div>
<dl class="grid max-w-4xl gap-px overflow-hidden rounded-2xl border border-slate-200 bg-slate-200 sm:grid-cols-2">
@foreach($fields as $field)
    @php
        $value = data_get($record, $field);
        if ($value instanceof \BackedEnum) {
            $enumKey = 'enums.'.$value->value;
            $translated = __($enumKey);
            $value = $translated === $enumKey ? \Illuminate\Support\Str::headline($value->value) : $translated;
        }
        $fieldKey = 'fields.'.$field;
        $fieldLabel = __($fieldKey);
        if ($fieldLabel === $fieldKey) {
            $fieldLabel = __(\Illuminate\Support\Str::headline(str_replace('_', ' ', $field)));
        }
    @endphp
    <div class="bg-white p-5"><dt class="text-xs font-semibold uppercase text-slate-500">{{ $fieldLabel }}</dt><dd class="mt-2 whitespace-pre-line">{{ $value ?: '—' }}</dd></div>
@endforeach
</dl>
@endsection

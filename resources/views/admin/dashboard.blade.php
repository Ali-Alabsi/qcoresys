@extends('layouts.admin')
@section('title', __('Dashboard'))
@section('content')
<div class="mb-7">
    <h1 class="text-2xl font-bold">{{ __('Dashboard') }}</h1>
    <p class="text-sm text-slate-500">{{ __('Business overview and quick access.') }}</p>
</div>
<div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
    @foreach($kpis as $label => $value)
        <div class="card-premium !p-5">
            <div class="text-sm text-slate-500">{{ $label }}</div>
            <div class="mt-2 text-3xl font-bold text-brand-navy">{{ is_numeric($value) ? number_format((float) $value, 2) : $value }}</div>
        </div>
    @endforeach
</div>
@endsection

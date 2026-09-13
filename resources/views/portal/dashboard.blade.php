@extends('layouts.portal')
@section('title', __('Dashboard'))
@section('content')
<div class="mb-7"><h1 class="text-2xl font-bold">{{ __('Welcome, :name', ['name' => $customer->name]) }}</h1><p class="text-sm text-slate-500">{{ __('Track your requests and commercial documents.') }}</p></div>
<div class="grid gap-5 sm:grid-cols-3">@foreach($kpis as $label=>$value)<div class="card-premium"><div class="text-sm text-slate-500">{{ $label }}</div><div class="mt-2 text-3xl font-bold">{{ number_format((float)$value, 2) }}</div></div>@endforeach</div>
<a href="{{ route('portal.requests.create') }}" class="btn-primary mt-7">{{ __('Submit a new request') }}</a>
@endsection

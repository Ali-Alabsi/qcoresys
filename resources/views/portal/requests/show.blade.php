@extends('layouts.portal')
@section('title', $customerRequest->request_no)
@section('content')
<h1 class="text-2xl font-bold">{{ $customerRequest->request_no }}</h1>
<p class="mb-6 text-sm text-slate-500">{{ $customerRequest->status->value }} · {{ $customerRequest->created_at->format('Y-m-d') }}</p>
<div class="max-w-4xl rounded-2xl border bg-white p-6">
    <h2 class="text-xl font-semibold">{{ $customerRequest->subject }}</h2>
    <p class="mt-4 whitespace-pre-line text-slate-700">{{ $customerRequest->description }}</p>
    <dl class="mt-6 grid gap-4 border-t pt-5 sm:grid-cols-3"><div><dt class="text-xs text-slate-500">{{ __('Type') }}</dt><dd>{{ $customerRequest->request_type->value }}</dd></div><div><dt class="text-xs text-slate-500">{{ __('Priority') }}</dt><dd>{{ $customerRequest->priority->value }}</dd></div><div><dt class="text-xs text-slate-500">{{ __('Service') }}</dt><dd>{{ $customerRequest->service?->name ?? '—' }}</dd></div></dl>
</div>
@endsection

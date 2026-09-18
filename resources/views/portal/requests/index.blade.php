@extends('layouts.portal')
@section('title', __('Requests'))
@section('content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-4">
  <h1 class="text-2xl font-bold">{{ __('Requests') }}</h1>
  <div class="flex flex-wrap items-center gap-3">
    @include('admin.shared.per-page', ['paginator' => $requests, 'selectId' => 'portal_requests_per_page'])
    <a class="btn-primary !px-4 !py-2" href="{{ route('portal.requests.create') }}">{{ __('New request') }}</a>
  </div>
</div>
<div class="overflow-x-auto rounded-2xl border bg-white"><table class="min-w-full text-sm"><thead class="bg-slate-50"><tr><th class="p-3 text-start">{{ __('Number') }}</th><th class="text-start">{{ __('Subject') }}</th><th>{{ __('Status') }}</th><th>{{ __('Created') }}</th></tr></thead>
<tbody class="divide-y">@forelse($requests as $record)<tr><td class="p-3"><a class="font-semibold text-cyan-600" href="{{ route('portal.requests.show',$record) }}">{{ $record->request_no }}</a></td><td>{{ $record->subject }}</td><td class="text-center">{{ $record->status->value }}</td><td class="text-center">{{ $record->created_at->toDateString() }}</td></tr>@empty<tr><td colspan="4" class="p-10 text-center text-slate-500">{{ __('No requests yet.') }}</td></tr>@endforelse</tbody></table></div>
<div class="mt-5">@include('admin.shared.pagination', ['paginator' => $requests])</div>
@endsection

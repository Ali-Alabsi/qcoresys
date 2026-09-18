@extends('layouts.portal')
@section('title', $title)
@section('content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-4">
  <h1 class="text-2xl font-bold">{{ $title }}</h1>
  @include('admin.shared.per-page', ['paginator' => $records, 'selectId' => 'portal_docs_per_page'])
</div>
<div class="overflow-x-auto rounded-2xl border bg-white"><table class="min-w-full text-sm"><thead class="bg-slate-50"><tr><th class="p-3 text-start">{{ __('Number') }}</th><th>{{ __('Date') }}</th><th>{{ __('Total') }}</th><th>{{ __('Status') }}</th></tr></thead>
<tbody class="divide-y">@forelse($records as $record)<tr><td class="p-3"><a class="font-semibold text-cyan-600" href="{{ route($routeBase.'.show',$record) }}">{{ $record->{$number} }}</a></td><td class="text-center">{{ ($record->quotation_date ?? $record->invoice_date)?->format('Y-m-d') }}</td><td class="text-center">USD {{ number_format((float)$record->total_amount,2) }}</td><td class="text-center">{{ $record->status->value }}</td></tr>@empty<tr><td colspan="4" class="p-10 text-center text-slate-500">{{ __('No documents found.') }}</td></tr>@endforelse</tbody></table></div>
<div class="mt-5">@include('admin.shared.pagination', ['paginator' => $records])</div>
@endsection

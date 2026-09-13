@extends('layouts.admin')
@section('title', $title)
@section('content')
@php
    use App\Enums\InvoiceStatus;
    use App\Enums\QuotationStatus;

    $status = $record->status;
    $canEdit = $kind === 'quotation'
        ? in_array($status, [QuotationStatus::Draft, QuotationStatus::InReview], true)
        : $status === InvoiceStatus::Draft;
    $canApprove = $kind === 'quotation'
        ? in_array($status, [QuotationStatus::Draft, QuotationStatus::InReview], true)
        : $status === InvoiceStatus::Draft;
    $canSend = $kind === 'quotation' && $status === QuotationStatus::Approved;
    $canPost = $kind === 'invoice' && $status === InvoiceStatus::Approved;
@endphp
<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <div><h1 class="text-2xl font-bold">{{ $title }}</h1><p class="text-sm text-slate-500">{{ $record->customer->name }} · {{ $record->status->value }}</p></div>
    <div class="flex flex-wrap gap-2">
        @if($canEdit && auth()->user()->hasPermission($kind.'s.update'))
            <a class="btn-secondary !px-4 !py-2" href="{{ route('admin.'.$kind.'s.edit', $record) }}">{{ __('Edit') }}</a>
        @endif
        <a class="btn-secondary !px-4 !py-2" target="_blank" href="{{ route('admin.'.$kind.'s.pdf', $record) }}?preview=1">{{ __('Preview') }}</a>
        <a class="btn-secondary !px-4 !py-2" target="_blank" href="{{ route('admin.'.$kind.'s.pdf', $record) }}?print=1">{{ __('Print') }}</a>
        <a class="btn-primary !px-4 !py-2" href="{{ route('admin.'.$kind.'s.pdf', $record) }}">{{ __('Export PDF') }}</a>
        @if($canApprove && auth()->user()->hasPermission($kind.'s.approve'))
            <form method="POST" action="{{ route('admin.'.$kind.'s.approve', $record) }}">@csrf<button class="btn-primary !px-4 !py-2">{{ __('Approve') }}</button></form>
        @endif
        @if($canSend && auth()->user()->hasPermission('quotations.send'))
            <form method="POST" action="{{ route('admin.quotations.send', $record) }}">@csrf<button class="btn-navy !px-4 !py-2">{{ __('Send') }}</button></form>
        @endif
        @if($canPost && auth()->user()->hasPermission('invoices.post'))
            <form method="POST" action="{{ route('admin.invoices.post', $record) }}">@csrf<button class="btn-navy !px-4 !py-2">{{ __('Post') }}</button></form>
        @endif
    </div>
</div>
<div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
    <table class="min-w-full text-sm"><thead class="bg-slate-50"><tr><th class="px-4 py-3 text-start">{{ __('Description') }}</th><th>{{ __('Price') }}</th><th>{{ __('Total') }}</th></tr></thead>
    <tbody class="divide-y">@foreach($record->items as $item)<tr><td class="px-4 py-3">{{ $item->description }}</td><td class="text-center">{{ number_format((float)$item->unit_price, 2) }}</td><td class="text-center">{{ number_format((float)($item->total_amount ?? $item->total), 2) }}</td></tr>@endforeach</tbody>
    <tfoot class="bg-slate-50 font-bold"><tr><td colspan="2" class="px-4 py-3 text-end">{{ __('Total') }}</td><td class="text-center">{{ $record->currency->code }} {{ number_format((float)$record->total_amount, 2) }}</td></tr></tfoot></table>
</div>
@endsection

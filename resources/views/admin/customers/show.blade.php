@extends('layouts.admin')
@section('title', $title)
@section('content')
@php
    $status = $customer->status;
    if ($status instanceof \BackedEnum) {
        $statusKey = 'enums.'.$status->value;
        $statusLabel = __($statusKey);
        if ($statusLabel === $statusKey) {
            $statusLabel = \Illuminate\Support\Str::headline($status->value);
        }
    } else {
        $statusLabel = $status ?: '—';
    }
@endphp

<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
  <div>
    <h1 class="text-2xl font-bold text-brand-navy">{{ $title }}</h1>
    <p class="text-sm text-slate-500">{{ $customer->customer_code }} · {{ $statusLabel }}</p>
  </div>
  <div class="flex flex-wrap gap-2">
    @if(auth()->user()->hasPermission('customers.update'))
      <a class="btn-secondary !px-4 !py-2" href="{{ route('admin.customers.edit', $customer) }}">{{ __('Edit') }}</a>
    @endif
    <button type="button" class="btn-secondary !px-4 !py-2" onclick="history.back()">{{ __('Back') }}</button>
  </div>
</div>

<div class="mb-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-4" style="gap: 1.25rem;">
  <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <p class="text-xs font-semibold uppercase text-slate-500">{{ __('Email') }}</p>
    <p class="mt-2 text-sm font-medium text-slate-800">{{ $customer->email ?: '—' }}</p>
  </div>
  <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <p class="text-xs font-semibold uppercase text-slate-500">{{ __('Phone') }}</p>
    <p class="mt-2 text-sm font-medium text-slate-800">{{ $customer->phone ?: '—' }}</p>
  </div>
  <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <p class="text-xs font-semibold uppercase text-slate-500">{{ __('fields.account.account_code') }}</p>
    <p class="mt-2 text-sm font-medium text-slate-800">
      @if($customer->account)
        {{ $customer->account->account_code }} — {{ $customer->account->localized_name }}
      @else
        —
      @endif
    </p>
  </div>
  <div class="rounded-2xl border border-cyan-200 bg-cyan-50 p-5 shadow-sm">
    <p class="text-xs font-semibold uppercase text-cyan-700">{{ __('Total outstanding') }}</p>
    <p class="mt-2 font-mono text-lg font-bold text-brand-navy">{{ number_format((float) $totalOutstanding, 2) }}</p>
  </div>
</div>

<div class="mb-8 grid gap-5 sm:grid-cols-2" style="gap: 1.25rem;">
  <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <p class="text-xs font-semibold uppercase text-slate-500">{{ __('Total invoiced') }}</p>
    <p class="mt-2 font-mono text-base font-bold text-slate-800">{{ number_format((float) $totalInvoiced, 2) }}</p>
  </div>
  <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <p class="text-xs font-semibold uppercase text-slate-500">{{ __('Paid') }}</p>
    <p class="mt-2 font-mono text-base font-bold text-emerald-700">{{ number_format((float) $totalPaid, 2) }}</p>
  </div>
</div>

<div class="mt-2">
  <h2 class="mb-4 text-lg font-bold text-brand-navy">{{ __('Customer invoices') }}</h2>
  <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white">
    <table class="min-w-full text-sm">
      <thead class="bg-slate-50 text-start text-xs uppercase text-slate-500">
        <tr>
          <th class="px-4 py-3 text-start">{{ __('Number') }}</th>
          <th class="px-4 py-3 text-start">{{ __('Date') }}</th>
          <th class="px-4 py-3 text-start">{{ __('Total') }}</th>
          <th class="px-4 py-3 text-start">{{ __('Paid') }}</th>
          <th class="px-4 py-3 text-start">{{ __('Balance') }}</th>
          <th class="px-4 py-3 text-start">{{ __('Status') }}</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        @forelse($invoices as $invoice)
          @php
            $invStatus = $invoice->status;
            if ($invStatus instanceof \BackedEnum) {
              $invKey = 'enums.'.$invStatus->value;
              $invStatusLabel = __($invKey);
              if ($invStatusLabel === $invKey) {
                $invStatusLabel = \Illuminate\Support\Str::headline($invStatus->value);
              }
            } else {
              $invStatusLabel = $invStatus;
            }
          @endphp
          <tr class="hover:bg-slate-50">
            <td class="whitespace-nowrap px-4 py-3">
              <a class="font-semibold text-cyan-600 hover:underline" href="{{ route('admin.invoices.show', $invoice) }}">{{ $invoice->invoice_no }}</a>
            </td>
            <td class="whitespace-nowrap px-4 py-3">{{ $invoice->invoice_date?->toDateString() }}</td>
            <td class="whitespace-nowrap px-4 py-3 font-mono">{{ number_format((float) $invoice->total_amount, 2) }}</td>
            <td class="whitespace-nowrap px-4 py-3 font-mono">{{ number_format((float) $invoice->paid_amount, 2) }}</td>
            <td class="whitespace-nowrap px-4 py-3 font-mono font-semibold text-cyan-700">{{ number_format((float) $invoice->remaining_amount, 2) }}</td>
            <td class="whitespace-nowrap px-4 py-3">{{ $invStatusLabel }}</td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="px-4 py-8 text-center text-slate-500">{{ __('No invoices yet.') }}</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection

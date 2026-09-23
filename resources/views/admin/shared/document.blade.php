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
    $currencyCode = $record->currency->code ?? '';
    $canRecordPayment = $kind === 'invoice'
        && auth()->user()->hasPermission('payments.create')
        && (float) $record->remaining_amount > 0
        && in_array($status, [InvoiceStatus::Posted, InvoiceStatus::PartiallyPaid, InvoiceStatus::Overdue], true);
@endphp
<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h1 class="text-2xl font-bold">{{ $title }}</h1>
        <p class="text-sm text-slate-500">
            @if($record->customer)
                <a class="font-semibold text-cyan-600 hover:underline" href="{{ route('admin.customers.show', $record->customer) }}">{{ $record->customer->name }}</a>
            @else
                —
            @endif
            @php
                if ($status instanceof \BackedEnum) {
                    $statusKey = 'enums.'.$status->value;
                    $statusText = __($statusKey);
                    if ($statusText === $statusKey) {
                        $statusText = \Illuminate\Support\Str::headline($status->value);
                    }
                } else {
                    $statusText = $status;
                }
            @endphp
            · {{ $statusText }}
        </p>
        @if($kind === 'invoice' && $record->account)
            <p class="mt-1 text-sm text-slate-600">{{ __('Revenue account') }}: {{ $record->account->account_code }} — {{ $record->account->localized_name }}</p>
        @endif
    </div>
    <div class="flex flex-wrap gap-2">
        @if($canEdit && auth()->user()->hasPermission($kind.'s.update'))
            <a class="btn-secondary !px-4 !py-2" href="{{ route('admin.'.$kind.'s.edit', $record) }}">{{ __('Edit') }}</a>
        @endif
        <a class="btn-secondary !px-4 !py-2" target="_blank" href="{{ route('admin.'.$kind.'s.pdf', $record) }}?preview=1">{{ __('Preview') }}</a>
        <a class="btn-secondary !px-4 !py-2" target="_blank" href="{{ route('admin.'.$kind.'s.pdf', $record) }}?print=1">{{ __('Print') }}</a>
        <a class="btn-primary !px-4 !py-2" href="{{ route('admin.'.$kind.'s.pdf', $record) }}" data-no-loading>{{ __('Export PDF') }}</a>
        @if($canApprove && auth()->user()->hasPermission($kind.'s.approve'))
            <form method="POST" action="{{ route('admin.'.$kind.'s.approve', $record) }}">@csrf<button class="btn-primary !px-4 !py-2">{{ __('Approve') }}</button></form>
        @endif
        @if($canSend && auth()->user()->hasPermission('quotations.send'))
            <form method="POST" action="{{ route('admin.quotations.send', $record) }}">@csrf<button class="btn-navy !px-4 !py-2">{{ __('Send') }}</button></form>
        @endif
        @if($canPost && auth()->user()->hasPermission('invoices.post'))
            <form method="POST" action="{{ route('admin.invoices.post', $record) }}" enctype="multipart/form-data" class="flex flex-wrap items-end gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
                @csrf
                <div>
                    <label for="invoice_vouchers" class="mb-1 block text-xs font-semibold text-slate-600">{{ __('Supporting documents') }} <span class="text-red-600">*</span></label>
                    <input id="invoice_vouchers" type="file" name="attachments[]" accept=".pdf,.png,.jpg,.jpeg" multiple required class="block max-w-[14rem] text-xs">
                </div>
                <button class="btn-navy !px-4 !py-2">{{ __('Post') }}</button>
            </form>
        @endif
        @if($canRecordPayment)
            <a class="btn-navy !px-4 !py-2" href="{{ route('admin.payments.index', ['new' => 1, 'invoice_id' => $record->id]) }}">{{ __('Record payment') }}</a>
        @endif
    </div>
</div>

@if($kind === 'invoice')
<div class="mb-6 grid gap-5 sm:grid-cols-3" style="gap: 1.25rem;">
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-xs font-semibold uppercase text-slate-500">{{ __('Total') }}</p>
        <p class="mt-2 font-mono text-lg font-bold text-brand-navy">{{ $currencyCode }} {{ number_format((float) $record->total_amount, 2) }}</p>
    </div>
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-xs font-semibold uppercase text-slate-500">{{ __('Paid') }}</p>
        <p class="mt-2 font-mono text-lg font-bold text-emerald-700">{{ $currencyCode }} {{ number_format((float) $record->paid_amount, 2) }}</p>
    </div>
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-xs font-semibold uppercase text-slate-500">{{ __('Balance') }}</p>
        <p class="mt-2 font-mono text-lg font-bold text-cyan-700">{{ $currencyCode }} {{ number_format((float) $record->remaining_amount, 2) }}</p>
    </div>
</div>
@endif

<div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
    <table class="min-w-full text-sm"><thead class="bg-slate-50"><tr><th class="px-4 py-3 text-start">{{ __('Description') }}</th><th>{{ __('Price') }}</th><th>{{ __('Total') }}</th></tr></thead>
    <tbody class="divide-y">@foreach($record->items as $item)<tr><td class="px-4 py-3">{{ $item->description }}</td><td class="text-center">{{ number_format((float)$item->unit_price, 2) }}</td><td class="text-center">{{ number_format((float)($item->total_amount ?? $item->total), 2) }}</td></tr>@endforeach</tbody>
    <tfoot class="bg-slate-50 font-bold"><tr><td colspan="2" class="px-4 py-3 text-end">{{ __('Total') }}</td><td class="text-center">{{ $currencyCode }} {{ number_format((float)$record->total_amount, 2) }}</td></tr></tfoot></table>
</div>

@if($kind === 'invoice')
<div class="mt-6">
    <h2 class="mb-3 text-lg font-bold text-brand-navy">{{ __('Payments for this invoice') }}</h2>
    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 text-start text-xs uppercase text-slate-500">
                <tr>
                    <th class="px-4 py-3 text-start">{{ __('Number') }}</th>
                    <th class="px-4 py-3 text-start">{{ __('Date') }}</th>
                    <th class="px-4 py-3 text-start">{{ __('Amount') }}</th>
                    <th class="px-4 py-3 text-start">{{ __('Method') }}</th>
                    <th class="px-4 py-3 text-start">{{ __('Deposit account') }}</th>
                    <th class="px-4 py-3 text-start">{{ __('Status') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse(($record->payments ?? collect()) as $payment)
                    @php
                        $pStatus = $payment->status;
                        if ($pStatus instanceof \BackedEnum) {
                            $pk = 'enums.'.$pStatus->value;
                            $pStatusLabel = __($pk) === $pk ? \Illuminate\Support\Str::headline($pStatus->value) : __($pk);
                        } else {
                            $pStatusLabel = $pStatus;
                        }
                        $pMethod = $payment->payment_method;
                        if ($pMethod instanceof \BackedEnum) {
                            $mk = 'enums.'.$pMethod->value;
                            $pMethodLabel = __($mk) === $mk ? \Illuminate\Support\Str::headline($pMethod->value) : __($mk);
                        } else {
                            $pMethodLabel = $pMethod;
                        }
                    @endphp
                    <tr class="hover:bg-slate-50">
                        <td class="whitespace-nowrap px-4 py-3">{{ $payment->payment_no }}</td>
                        <td class="whitespace-nowrap px-4 py-3">{{ $payment->payment_date?->toDateString() }}</td>
                        <td class="whitespace-nowrap px-4 py-3 font-mono">{{ number_format((float) $payment->amount, 2) }}</td>
                        <td class="whitespace-nowrap px-4 py-3">{{ $pMethodLabel }}</td>
                        <td class="whitespace-nowrap px-4 py-3">
                            @if($payment->account)
                                {{ $payment->account->account_code }} — {{ $payment->account->localized_name }}
                            @else
                                —
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-4 py-3">{{ $pStatusLabel }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-slate-500">{{ __('No payments yet.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endif
@endsection

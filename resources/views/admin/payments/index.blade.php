@extends('layouts.admin')
@section('title', __('Payments'))
@section('content')
@php
    use App\Enums\PaymentStatus;

    $canCreate = auth()->user()->hasPermission('payments.create');
    $canPost = auth()->user()->hasPermission('payments.post');
    $openCreateModal = $canCreate && (request()->boolean('new') || $errors->any());
    $selectedInvoiceId = (string) old('invoice_id', request('invoice_id', ''));
@endphp
<style>
  #paymentModal {
    display: none;
    position: fixed;
    inset: 0;
    z-index: 100;
    align-items: center;
    justify-content: center;
    padding: 3rem 1rem;
    box-sizing: border-box;
  }
  #paymentModal.is-open {
    display: flex;
  }
  #paymentModalBackdrop {
    position: absolute;
    inset: 0;
    background: rgba(15, 23, 42, 0.45);
  }
  #paymentModalPanel {
    position: relative;
    z-index: 1;
    width: min(100%, 560px);
    max-height: calc(100vh - 6rem);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 25px 50px rgba(0,0,0,.25);
    border: 1px solid #e2e8f0;
  }
  #paymentModalPanel .modal-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 16px;
    border-bottom: 1px solid #f1f5f9;
    flex-shrink: 0;
  }
  #paymentForm {
    padding: 14px 16px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 12px;
  }
  #paymentForm .row-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
  }
  #paymentForm .field label {
    display: block;
    margin-bottom: 4px;
    font-size: 11px;
    font-weight: 700;
    color: #475569;
  }
  @media (max-width: 640px) {
    #paymentForm .row-2 {
      grid-template-columns: 1fr;
    }
  }
</style>

<div class="mb-6 flex flex-wrap items-center justify-between gap-4">
  <div class="flex items-center gap-3">
    <h1 class="text-2xl font-bold text-brand-navy">{{ __('Payments') }}</h1>
    <span class="rounded-full bg-slate-200 px-3 py-1 font-mono text-xs text-slate-600">{{ $payments->total() }}</span>
  </div>
  <div class="flex flex-wrap items-center gap-3">
    @include('admin.shared.per-page', ['paginator' => $payments])
    @if($canCreate)
      <button type="button" id="btnOpenPayment" class="btn-primary !px-4 !py-2">{{ __('New payment') }}</button>
    @endif
  </div>
</div>

<div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white">
  <table class="min-w-full text-sm">
    <thead class="bg-slate-50 text-start text-xs uppercase text-slate-500">
      <tr>
        <th class="px-4 py-3 text-start">{{ __('Number') }}</th>
        <th class="px-4 py-3 text-start">{{ __('Customer') }}</th>
        <th class="px-4 py-3 text-start">{{ __('Invoice') }}</th>
        <th class="px-4 py-3 text-start">{{ __('Date') }}</th>
        <th class="px-4 py-3 text-start">{{ __('Amount') }}</th>
        <th class="px-4 py-3 text-start">{{ __('Status') }}</th>
        <th class="px-4 py-3"></th>
      </tr>
    </thead>
    <tbody class="divide-y divide-slate-100">
      @forelse($payments as $payment)
        @php
          $status = $payment->status;
          $statusLabel = $status instanceof \BackedEnum
            ? (($_t = __('enums.'.$status->value)) === 'enums.'.$status->value ? \Illuminate\Support\Str::headline($status->value) : $_t)
            : $status;
          $canPostRecord = $canPost && $status === PaymentStatus::Draft;
        @endphp
        <tr class="hover:bg-slate-50">
          <td class="whitespace-nowrap px-4 py-3">{{ $payment->payment_no }}</td>
          <td class="whitespace-nowrap px-4 py-3">
            @if($payment->customer)
              <a class="font-semibold text-cyan-600 hover:underline" href="{{ route('admin.customers.show', $payment->customer) }}">{{ $payment->customer->name }}</a>
            @else
              —
            @endif
          </td>
          <td class="whitespace-nowrap px-4 py-3">
            @if($payment->invoice)
              <a class="font-semibold text-cyan-600 hover:underline" href="{{ route('admin.invoices.show', $payment->invoice) }}">{{ $payment->invoice->invoice_no }}</a>
            @else
              —
            @endif
          </td>
          <td class="whitespace-nowrap px-4 py-3">{{ $payment->payment_date?->toDateString() }}</td>
          <td class="whitespace-nowrap px-4 py-3">{{ number_format((float) $payment->amount, 2) }}</td>
          <td class="whitespace-nowrap px-4 py-3">{{ $statusLabel }}</td>
          <td class="whitespace-nowrap px-4 py-3">
            <div class="flex flex-row flex-nowrap items-center justify-end gap-3">
              @if($canPostRecord)
                <form class="inline" method="POST" action="{{ route('admin.payments.post', $payment) }}">
                  @csrf
                  <button type="submit" class="font-semibold text-brand-navy">{{ __('Post') }}</button>
                </form>
              @endif
            </div>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="7" class="px-4 py-10 text-center text-slate-500">{{ __('No records found.') }}</td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>

@include('admin.shared.pagination', ['paginator' => $payments])

@if($canCreate)
<div id="paymentModal" class="{{ $openCreateModal ? 'is-open' : '' }}" aria-hidden="{{ $openCreateModal ? 'false' : 'true' }}">
  <div id="paymentModalBackdrop"></div>
  <div id="paymentModalPanel" role="dialog" aria-modal="true" aria-labelledby="paymentModalTitle">
    <div class="modal-head">
      <h2 id="paymentModalTitle" class="text-sm font-bold text-brand-navy">{{ __('New payment') }}</h2>
      <button type="button" id="btnClosePayment" class="flex h-8 w-8 items-center justify-center rounded-md text-slate-500 hover:bg-slate-100" aria-label="{{ __('Cancel') }}">✕</button>
    </div>
    <form id="paymentForm" method="POST" action="{{ route('admin.payments.store') }}">
      @csrf
      <div class="field">
        <label for="invoice_id">{{ __('Invoice') }}</label>
        <select class="input-public !px-2 !py-1.5 text-sm" id="invoice_id" name="invoice_id" required>
          <option value="">{{ __('Select') }}</option>
          @foreach ($outstandingInvoices as $invoice)
            @php $remaining = number_format((float) $invoice->remaining_amount, 2, '.', ''); @endphp
            <option
              value="{{ $invoice->id }}"
              data-remaining="{{ $remaining }}"
              @selected($selectedInvoiceId === (string) $invoice->id)
            >{{ $invoice->invoice_no }} — {{ __('Remaining balance: :amount', ['amount' => $remaining]) }}</option>
          @endforeach
        </select>
        @error('invoice_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
      </div>
      <div class="field">
        <label for="account_id">{{ __('Deposit account') }}</label>
        <select class="input-public !px-2 !py-1.5 text-sm" id="account_id" name="account_id" required>
          <option value="">{{ __('Select') }}</option>
          @foreach ($depositAccounts as $id => $label)
            <option value="{{ $id }}" @selected((string) old('account_id') === (string) $id)>{{ $label }}</option>
          @endforeach
        </select>
        @error('account_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
      </div>
      <div class="row-2">
        <div class="field">
          <label for="payment_method">{{ __('Method') }}</label>
          <select class="input-public !px-2 !py-1.5 text-sm" id="payment_method" name="payment_method" required>
            <option value="">{{ __('Select') }}</option>
            @foreach ($paymentMethods as $value => $label)
              <option value="{{ $value }}" @selected((string) old('payment_method') === (string) $value)>{{ $label }}</option>
            @endforeach
          </select>
          @error('payment_method')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div class="field">
          <label for="payment_date">{{ __('Date') }}</label>
          <input class="input-public !px-2 !py-1.5 text-sm" type="date" id="payment_date" name="payment_date" value="{{ old('payment_date', now()->toDateString()) }}" required>
          @error('payment_date')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
      </div>
      <div class="row-2">
        <div class="field">
          <label for="amount">{{ __('Amount') }}</label>
          <input class="input-public !px-2 !py-1.5 font-mono text-sm" type="number" step="0.01" min="0.01" id="amount" name="amount" value="{{ old('amount') }}" required>
          <p id="amountRemainingHint" class="mt-1 hidden text-xs text-slate-500"></p>
          @error('amount')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div class="field">
          <label for="reference_no">{{ __('Reference') }}</label>
          <input class="input-public !px-2 !py-1.5 text-sm" type="text" id="reference_no" name="reference_no" value="{{ old('reference_no') }}">
          @error('reference_no')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
      </div>
      <div class="mt-1 flex gap-2">
        <button type="submit" class="btn-primary flex-1 !py-2 text-sm">{{ __('Save and post') }}</button>
        <button type="button" id="btnCancelPayment" class="btn-secondary !py-2 text-sm">{{ __('Cancel') }}</button>
      </div>
    </form>
  </div>
</div>
@endif
@endsection

@if($canCreate)
@push('scripts')
<script>
(function () {
  const modal = document.getElementById('paymentModal');
  if (!modal) return;

  const invoiceSelect = document.getElementById('invoice_id');
  const amountInput = document.getElementById('amount');
  const amountHint = document.getElementById('amountRemainingHint');
  const remainingLabel = @json(__('Remaining balance: :amount', ['amount' => '__AMOUNT__']));

  function fillAmountFromInvoice(forceFill) {
    if (!invoiceSelect || !amountInput) return;
    const option = invoiceSelect.options[invoiceSelect.selectedIndex];
    const remaining = option?.getAttribute('data-remaining');
    if (!remaining) {
      amountInput.removeAttribute('max');
      if (amountHint) {
        amountHint.classList.add('hidden');
        amountHint.textContent = '';
      }
      return;
    }
    amountInput.max = remaining;
    if (amountHint) {
      amountHint.textContent = remainingLabel.replace('__amount__', remaining);
      amountHint.classList.remove('hidden');
    }
    if (forceFill || !amountInput.value) {
      amountInput.value = remaining;
    }
  }

  function openModal() {
    modal.classList.add('is-open');
    modal.setAttribute('aria-hidden', 'false');
    document.body.classList.add('overflow-hidden');
    invoiceSelect?.focus();
  }

  function closeModal() {
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('overflow-hidden');
  }

  document.getElementById('btnOpenPayment')?.addEventListener('click', openModal);
  document.getElementById('btnClosePayment')?.addEventListener('click', closeModal);
  document.getElementById('btnCancelPayment')?.addEventListener('click', closeModal);
  document.getElementById('paymentModalBackdrop')?.addEventListener('click', closeModal);
  invoiceSelect?.addEventListener('change', () => fillAmountFromInvoice(true));
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && modal.classList.contains('is-open') && !document.documentElement.classList.contains('app-dialog-open')) {
      closeModal();
    }
  });

  fillAmountFromInvoice(false);
  if (modal.classList.contains('is-open')) {
    document.body.classList.add('overflow-hidden');
  }
})();
</script>
@endpush
@endif

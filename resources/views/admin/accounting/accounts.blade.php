@extends('layouts.admin')
@section('title', __('Accounts'))
@section('content')
@php
    $typeCounts = collect($accounts)->countBy(fn ($acc) => $acc['type']);
    $canCreate = auth()->user()->hasPermission('accounts.create');
    $openCreateModal = $canCreate && (request()->boolean('new') || $errors->any());
@endphp
<style>
  .acc-filter-chip {
    border: 2px solid transparent;
    cursor: pointer;
  }
  .acc-filter-chip.is-active {
    border-color: #091a2f;
    box-shadow: 0 0 0 3px rgba(0, 210, 255, 0.55);
    transform: translateY(-1px);
  }
  .acc-filter-chip.is-active-all {
    background: #091a2f;
    color: #ffffff;
  }
  #accountModal {
    display: none;
    position: fixed;
    inset: 0;
    z-index: 100;
    align-items: center;
    justify-content: center;
    padding: 3rem 1rem;
    box-sizing: border-box;
  }
  #accountModal.is-open {
    display: flex;
  }
  #accountModalBackdrop {
    position: absolute;
    inset: 0;
    background: rgba(15, 23, 42, 0.45);
  }
  #accountModalPanel {
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
  #accountModalPanel .modal-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 16px;
    border-bottom: 1px solid #f1f5f9;
    flex-shrink: 0;
  }
  #accountForm {
    padding: 14px 16px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 12px;
  }
  #accountForm .row-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
  }
  #accountForm .field label {
    display: block;
    margin-bottom: 4px;
    font-size: 11px;
    font-weight: 700;
    color: #475569;
  }
  @media (max-width: 640px) {
    #accountForm .row-2 {
      grid-template-columns: 1fr;
    }
  }
</style>
<div
  x-data="{
    filter: 'all',
    query: '',
    accounts: {{ \Illuminate\Support\Js::from($accounts) }},
    counts: {{ \Illuminate\Support\Js::from($typeCounts) }},
    setFilter(type) {
      this.filter = this.filter === type ? 'all' : type
    },
    matches(acc) {
      if (!acc) return false
      if (this.filter !== 'all' && this.filter !== acc.type) return false
      const q = this.query.trim().toLowerCase()
      if (!q) return true
      return String(acc.code).toLowerCase().includes(q)
        || String(acc.name).toLowerCase().includes(q)
    },
    visibleCount() {
      return this.accounts.filter(acc => this.matches(acc)).length
    }
  }"
>
  <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
    <div class="flex items-center gap-3">
      <h1 class="text-2xl font-bold text-brand-navy">{{ __('Accounts') }}</h1>
      <span class="rounded-full bg-slate-200 px-3 py-1 font-mono text-xs text-slate-600" x-text="visibleCount()">{{ count($accounts) }}</span>
    </div>
    @if($canCreate)
      <button type="button" id="btnOpenAccount" class="btn-primary !px-4 !py-2">{{ __('New account') }}</button>
    @endif
  </div>

  <div class="mb-4">
    <label class="sr-only" for="accounts-search">{{ __('Search accounts...') }}</label>
    <input
      id="accounts-search"
      type="search"
      x-model="query"
      placeholder="{{ __('Search accounts...') }}"
      class="input-public w-full max-w-md"
      autocomplete="off"
    >
  </div>

  <div class="mb-4 flex flex-wrap gap-2">
    <button
      type="button"
      @click="filter = 'all'"
      class="acc-filter-chip rounded-full bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-600"
      :class="{ 'is-active': filter === 'all', 'is-active-all': filter === 'all' }"
      :aria-pressed="filter === 'all'"
    >
      {{ __('All') }}
      <span class="ms-1 font-mono">{{ count($accounts) }}</span>
    </button>
    @foreach (\App\Enums\AccountType::cases() as $type)
      <button
        type="button"
        @click="setFilter('{{ $type->value }}')"
        class="acc-filter-chip rounded-full px-3 py-1.5 text-xs font-bold {{ $type->badgeClasses() }}"
        :class="{ 'is-active': filter === '{{ $type->value }}' }"
        :aria-pressed="filter === '{{ $type->value }}'"
      >
        {{ $type->label() }}
        <span class="ms-1 font-mono">{{ $typeCounts[$type->value] ?? 0 }}</span>
      </button>
    @endforeach
  </div>

  <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="border-b border-slate-200 bg-slate-50 px-5 py-3 text-sm font-bold text-brand-navy">
      {{ __('Chart of accounts') }}
    </div>
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-slate-50 text-xs uppercase text-slate-500">
          <tr>
            <th class="px-4 py-3 text-start">{{ __('Account Code') }}</th>
            <th class="px-4 py-3 text-start">{{ __('Account Name') }}</th>
            <th class="px-4 py-3 text-start">{{ __('Account type') }}</th>
            <th class="px-4 py-3 text-end">{{ __('Current Balance') }}</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          @forelse ($accounts as $index => $acc)
            @php
              $accountType = \App\Enums\AccountType::tryFrom($acc['type'] ?? '');
            @endphp
            <tr class="hover:bg-slate-50" x-show="matches(accounts[{{ $index }}])">
              <td class="px-4 py-3 font-mono text-xs font-bold text-slate-500">{{ $acc['code'] }}</td>
              <td class="px-4 py-3 font-medium">{{ $acc['name'] }}</td>
              <td class="px-4 py-3">
                @if ($accountType)
                  <button
                    type="button"
                    @click="setFilter('{{ $accountType->value }}')"
                    class="acc-filter-chip inline-flex rounded-full px-2.5 py-0.5 text-[11px] font-bold {{ $accountType->badgeClasses() }}"
                    :class="{ 'is-active': filter === '{{ $accountType->value }}' }"
                    :aria-pressed="filter === '{{ $accountType->value }}'"
                  >
                    {{ $acc['type_label'] }}
                  </button>
                @endif
              </td>
              <td class="px-4 py-3 text-end font-mono text-xs font-bold {{ $acc['balance'] > 0 ? 'text-emerald-600' : 'text-slate-400' }}">
                {{ number_format($acc['balance'], 2) }}
              </td>
            </tr>
          @empty
            <tr><td colspan="4" class="px-4 py-8 text-center text-slate-500">{{ __('No active accounts.') }}</td></tr>
          @endforelse
          @if (count($accounts) > 0)
            <tr x-show="visibleCount() === 0" x-cloak>
              <td colspan="4" class="px-4 py-8 text-center text-slate-500">{{ __('No matching accounts.') }}</td>
            </tr>
          @endif
        </tbody>
      </table>
    </div>
  </div>
</div>

@if($canCreate)
<div id="accountModal" class="{{ $openCreateModal ? 'is-open' : '' }}" aria-hidden="{{ $openCreateModal ? 'false' : 'true' }}">
  <div id="accountModalBackdrop"></div>
  <div id="accountModalPanel" role="dialog" aria-modal="true" aria-labelledby="accountModalTitle">
    <div class="modal-head">
      <h2 id="accountModalTitle" class="text-sm font-bold text-brand-navy">{{ __('New account') }}</h2>
      <button type="button" id="btnCloseAccount" class="flex h-8 w-8 items-center justify-center rounded-md text-slate-500 hover:bg-slate-100" aria-label="{{ __('Cancel') }}">✕</button>
    </div>
    <form id="accountForm" method="POST" action="{{ route('admin.accounts.store') }}">
      @csrf
      <div class="row-2">
        <div class="field">
          <label for="account_code">{{ __('Account Code') }}</label>
          <input class="input-public !px-2 !py-1.5 font-mono text-sm" type="text" id="account_code" name="account_code" value="{{ old('account_code') }}" required>
          @error('account_code')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div class="field">
          <label for="account_type">{{ __('Account type') }}</label>
          <select class="input-public !px-2 !py-1.5 text-sm" id="account_type" name="account_type" required>
            <option value="">{{ __('Select') }}</option>
            @foreach ($accountTypes as $value => $label)
              <option value="{{ $value }}" @selected((string) old('account_type') === (string) $value)>{{ $label }}</option>
            @endforeach
          </select>
          @error('account_type')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
      </div>
      <div class="field">
        <label for="account_name">{{ __('Account Name') }}</label>
        <input class="input-public !px-2 !py-1.5 text-sm" type="text" id="account_name" name="account_name" value="{{ old('account_name') }}" required>
        @error('account_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
      </div>
      <div class="field">
        <label for="account_name_ar">{{ __('Arabic name') }}</label>
        <input class="input-public !px-2 !py-1.5 text-sm" type="text" id="account_name_ar" name="account_name_ar" value="{{ old('account_name_ar') }}">
        @error('account_name_ar')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
      </div>
      <div class="row-2">
        <div class="field">
          <label for="parent_id">{{ __('Parent account') }}</label>
          <select class="input-public !px-2 !py-1.5 text-sm" id="parent_id" name="parent_id">
            <option value="">{{ __('Select') }}</option>
            @foreach ($parentAccounts as $id => $label)
              <option value="{{ $id }}" @selected((string) old('parent_id') === (string) $id)>{{ $label }}</option>
            @endforeach
          </select>
          @error('parent_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div class="field">
          <label for="currency_id">{{ __('Currency') }}</label>
          <select class="input-public !px-2 !py-1.5 text-sm" id="currency_id" name="currency_id">
            <option value="">{{ __('Select') }}</option>
            @foreach ($currencies as $id => $code)
              <option value="{{ $id }}" @selected((string) old('currency_id') === (string) $id)>{{ $code }}</option>
            @endforeach
          </select>
          @error('currency_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
      </div>
      <div class="flex flex-wrap gap-4 pt-1">
        <label class="flex items-center gap-2 text-sm font-medium text-slate-700">
          <input type="checkbox" name="is_cash_account" value="1" @checked(old('is_cash_account'))>
          {{ __('Cash account') }}
        </label>
        <label class="flex items-center gap-2 text-sm font-medium text-slate-700">
          <input type="checkbox" name="is_bank_account" value="1" @checked(old('is_bank_account'))>
          {{ __('Bank account') }}
        </label>
      </div>
      <div class="mt-1 flex gap-2">
        <button type="submit" class="btn-primary flex-1 !py-2 text-sm">{{ __('Save') }}</button>
        <button type="button" id="btnCancelAccount" class="btn-secondary !py-2 text-sm">{{ __('Cancel') }}</button>
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
  const modal = document.getElementById('accountModal');
  if (!modal) return;

  function openModal() {
    modal.classList.add('is-open');
    modal.setAttribute('aria-hidden', 'false');
    document.body.classList.add('overflow-hidden');
    document.getElementById('account_code')?.focus();
  }

  function closeModal() {
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('overflow-hidden');
  }

  document.getElementById('btnOpenAccount')?.addEventListener('click', openModal);
  document.getElementById('btnCloseAccount')?.addEventListener('click', closeModal);
  document.getElementById('btnCancelAccount')?.addEventListener('click', closeModal);
  document.getElementById('accountModalBackdrop')?.addEventListener('click', closeModal);
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && modal.classList.contains('is-open') && !document.documentElement.classList.contains('app-dialog-open')) {
      closeModal();
    }
  });

  if (modal.classList.contains('is-open')) {
    document.body.classList.add('overflow-hidden');
  }
})();
</script>
@endpush
@endif

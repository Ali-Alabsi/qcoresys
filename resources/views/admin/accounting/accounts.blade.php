@extends('layouts.admin')
@section('title', __('Accounts'))
@section('content')
@php
    $typeCounts = collect($accounts)->countBy(fn ($acc) => $acc['type']);
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
</style>
<div
  x-data="{
    filter: 'all',
    counts: {{ \Illuminate\Support\Js::from($typeCounts) }},
    total: {{ count($accounts) }},
    setFilter(type) {
      this.filter = this.filter === type ? 'all' : type
    },
    visibleCount() {
      return this.filter === 'all' ? this.total : (this.counts[this.filter] || 0)
    }
  }"
>
  <div class="mb-6 flex items-center justify-between gap-4">
    <h1 class="text-2xl font-bold text-brand-navy">{{ __('Accounts') }}</h1>
    <span class="rounded-full bg-slate-200 px-3 py-1 font-mono text-xs text-slate-600" x-text="visibleCount()">{{ count($accounts) }}</span>
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
          @forelse ($accounts as $acc)
            @php
              $accountType = \App\Enums\AccountType::tryFrom($acc['type'] ?? '');
            @endphp
            <tr class="hover:bg-slate-50" x-show="filter === 'all' || filter === '{{ $acc['type'] }}'">
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
              <td colspan="4" class="px-4 py-8 text-center text-slate-500">{{ __('No active accounts.') }}</td>
            </tr>
          @endif
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection

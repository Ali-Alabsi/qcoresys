@extends('layouts.admin')
@section('title', __('Account statement').' — '.$account->account_code)
@section('content')
@php
  $exportQuery = array_filter([
      'from' => $from,
      'to' => $to,
  ]);
  $isFirstPage = ! isset($paginator) || $paginator->onFirstPage();
  $pageOpening = (float) ($ledger['page_opening_balance'] ?? $ledger['opening_balance']);
@endphp
<div class="mb-6 flex flex-wrap items-start justify-between gap-4">
  <div>
    <a href="{{ route('admin.accounts.index') }}" class="mb-2 inline-block text-sm font-medium text-cyan-700 hover:underline">← {{ __('Back to accounts') }}</a>
    <h1 class="text-2xl font-bold text-brand-navy">{{ __('Account statement') }}</h1>
    <p class="mt-1 font-mono text-sm text-slate-600">
      <span class="font-bold text-cyan-700">{{ $account->account_code }}</span>
      — {{ $account->localized_name }}
    </p>
  </div>
  <div class="flex flex-wrap gap-2">
    <a href="{{ route('admin.accounts.ledger.pdf', ['account' => $account] + $exportQuery) }}" class="btn-secondary !px-4 !py-2 text-sm" data-no-loading>{{ __('Export PDF') }}</a>
    <a href="{{ route('admin.accounts.ledger.excel', ['account' => $account] + $exportQuery) }}" class="btn-primary !px-4 !py-2 text-sm" data-no-loading>{{ __('Export Excel') }}</a>
  </div>
</div>

<form method="GET" action="{{ route('admin.accounts.ledger', $account) }}" class="mb-4 flex flex-wrap items-end gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
  <div>
    <label for="ledger-from" class="mb-1 block text-xs font-bold text-slate-500">{{ __('From') }}</label>
    <input id="ledger-from" type="date" name="from" value="{{ $from }}" class="input-public !px-2 !py-1.5 text-sm">
  </div>
  <div>
    <label for="ledger-to" class="mb-1 block text-xs font-bold text-slate-500">{{ __('To') }}</label>
    <input id="ledger-to" type="date" name="to" value="{{ $to }}" class="input-public !px-2 !py-1.5 text-sm">
  </div>
  <div>
    <label for="ledger_per_page" class="mb-1 block text-xs font-bold text-slate-500">{{ __('Per page') }}</label>
    <select
      id="ledger_per_page"
      name="per_page"
      class="input-public !w-auto !px-2 !py-1.5 text-sm"
    >
      @foreach ([10, 20, 30, 50] as $option)
        <option value="{{ $option }}" @selected((int) ($paginator->perPage() ?? 20) === (int) $option)>{{ $option }}</option>
      @endforeach
    </select>
  </div>
  <button type="submit" class="btn-secondary !px-4 !py-2 text-sm">{{ __('Apply') }}</button>
</form>

<div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
  <div class="overflow-x-auto">
    <table class="min-w-full text-sm">
      <thead class="bg-slate-50 text-xs uppercase text-slate-500">
        <tr>
          <th class="px-4 py-3 text-start">{{ __('Date') }}</th>
          <th class="px-4 py-3 text-start">{{ __('Reference') }}</th>
          <th class="px-4 py-3 text-start">{{ __('Description') }}</th>
          <th class="px-4 py-3 text-end">{{ __('Debit') }}</th>
          <th class="px-4 py-3 text-end">{{ __('Credit') }}</th>
          <th class="px-4 py-3 text-end">{{ __('Balance') }}</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        <tr class="bg-slate-50 font-semibold">
          <td class="px-4 py-3" colspan="3">
            {{ $isFirstPage ? __('Opening balance') : __('Balance brought forward') }}
          </td>
          <td class="px-4 py-3 text-end font-mono text-xs">—</td>
          <td class="px-4 py-3 text-end font-mono text-xs">—</td>
          <td class="px-4 py-3 text-end font-mono text-xs">{{ number_format($pageOpening, 2) }}</td>
        </tr>
        @forelse ($ledger['lines'] as $line)
          <tr class="hover:bg-slate-50">
            <td class="px-4 py-3 whitespace-nowrap">{{ $line['entry_date'] }}</td>
            <td class="px-4 py-3 font-mono text-xs font-bold text-cyan-700">
              @if (!empty($line['entry_id']))
                <a href="{{ route('admin.journals.show', $line['entry_id']) }}" class="hover:underline">{{ $line['entry_no'] }}</a>
              @else
                {{ $line['entry_no'] }}
              @endif
            </td>
            <td class="px-4 py-3">{{ $line['description'] }}</td>
            <td class="px-4 py-3 text-end font-mono text-xs">{{ $line['debit'] > 0 ? number_format((float) $line['debit'], 2) : '—' }}</td>
            <td class="px-4 py-3 text-end font-mono text-xs">{{ $line['credit'] > 0 ? number_format((float) $line['credit'], 2) : '—' }}</td>
            <td class="px-4 py-3 text-end font-mono text-xs font-bold">{{ number_format((float) $line['running_balance'], 2) }}</td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="px-4 py-8 text-center text-slate-500">{{ __('No ledger entries.') }}</td>
          </tr>
        @endforelse
      </tbody>
      <tfoot class="bg-slate-50 font-bold">
        <tr>
          <td class="px-4 py-3" colspan="3">{{ __('Closing balance') }}</td>
          <td class="px-4 py-3 text-end font-mono text-xs">{{ number_format((float) ($ledger['total_debit'] ?? 0), 2) }}</td>
          <td class="px-4 py-3 text-end font-mono text-xs">{{ number_format((float) ($ledger['total_credit'] ?? 0), 2) }}</td>
          <td class="px-4 py-3 text-end font-mono text-xs text-emerald-700">{{ number_format((float) $ledger['closing_balance'], 2) }}</td>
        </tr>
      </tfoot>
    </table>
  </div>
</div>

@if (isset($paginator))
  @include('admin.shared.pagination', ['paginator' => $paginator])
@endif
@endsection

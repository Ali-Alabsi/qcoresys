@extends('layouts.admin')
@section('title', __('Exchange rates'))
@section('content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <h1 class="text-2xl font-bold">{{ __('Exchange rates') }}</h1>
    @if(auth()->user()->hasPermission('exchange_rates.create'))
        <a href="{{ route('admin.exchange-rates.create') }}" class="btn-primary">{{ __('New exchange rate') }}</a>
    @endif
</div>

@if(auth()->user()->hasPermission('journals.create'))
<div class="mb-6 rounded-2xl border border-slate-200 bg-white p-5">
    <h2 class="mb-2 text-lg font-semibold">{{ __('Annual FX closing') }}</h2>
    <p class="mb-4 text-sm text-slate-600">{{ __('Create a year-end revaluation draft for foreign cash and bank accounts using closing rates as of 31 December.') }}</p>
    <form method="POST" action="{{ route('admin.exchange-rates.annual-closing') }}" class="flex flex-wrap items-end gap-3">
        @csrf
        <div>
            <label class="label-public" for="year">{{ __('Closing year') }}</label>
            <input class="input-public w-32" id="year" type="number" name="year" min="2000" max="2100" value="{{ old('year', $currentYear) }}" required>
        </div>
        <button class="btn-navy">{{ __('Create annual closing draft') }}</button>
    </form>
</div>
@endif

<div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white">
    <table class="min-w-full text-sm">
        <thead class="bg-slate-50 text-slate-600">
            <tr>
                <th class="p-3 text-start">{{ __('Date') }}</th>
                <th class="p-3 text-start">{{ __('From') }}</th>
                <th class="p-3 text-start">{{ __('To') }}</th>
                <th class="p-3 text-start">{{ __('Rate') }}</th>
                <th class="p-3 text-start">{{ __('Type') }}</th>
                <th class="p-3 text-start">{{ __('Source') }}</th>
                <th class="p-3 text-start">{{ __('Active') }}</th>
                <th class="p-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y">
            @forelse($rates as $rate)
                <tr>
                    <td class="p-3">{{ $rate->rate_date?->toDateString() }}</td>
                    <td class="p-3">{{ $rate->fromCurrency?->code }}</td>
                    <td class="p-3">{{ $rate->toCurrency?->code }}</td>
                    <td class="p-3 font-mono">{{ rtrim(rtrim(number_format((float) $rate->rate, 10, '.', ''), '0'), '.') }}</td>
                    <td class="p-3">{{ $rate->rate_type?->value }}</td>
                    <td class="p-3">{{ $rate->source }}</td>
                    <td class="p-3">{{ $rate->is_active ? __('Yes') : __('No') }}</td>
                    <td class="p-3 text-end">
                        @if(auth()->user()->hasPermission('exchange_rates.update'))
                            <a class="text-brand-navy underline" href="{{ route('admin.exchange-rates.edit', $rate) }}">{{ __('Edit') }}</a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="p-6 text-center text-slate-500">{{ __('No exchange rates yet.') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $rates->links() }}</div>
@endsection

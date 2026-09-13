@extends('layouts.admin')
@section('title', $journal->entry_no)
@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold">{{ $journal->entry_no }}</h1>
        <p class="text-sm text-slate-500">{{ $journal->description }} · {{ $journal->status->value }} · {{ $journal->entry_date?->toDateString() }}</p>
    </div>
    @if(auth()->user()->hasPermission('journals.post') && $journal->status->value === 'DRAFT')
        <form method="POST" action="{{ route('admin.journals.post', $journal) }}">@csrf<button class="btn-primary">{{ __('Post') }}</button></form>
    @endif
</div>
<div class="overflow-x-auto rounded-2xl border bg-white">
    <table class="min-w-full text-sm">
        <thead class="bg-slate-50">
            <tr>
                <th class="p-3 text-start">{{ __('Account') }}</th>
                <th class="p-3 text-start">{{ __('Currency') }}</th>
                <th class="p-3 text-start">{{ __('Rate') }}</th>
                <th class="p-3 text-start">{{ __('Description') }}</th>
                <th class="p-3 text-center">{{ __('Debit') }}</th>
                <th class="p-3 text-center">{{ __('Credit') }}</th>
                <th class="p-3 text-center">{{ __('Debit base') }}</th>
                <th class="p-3 text-center">{{ __('Credit base') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y">
            @foreach($journal->lines as $line)
                <tr>
                    <td class="p-3">{{ $line->account->account_code }} — {{ $line->account->localized_name }}</td>
                    <td class="p-3">{{ $line->currency?->code }}</td>
                    <td class="p-3 font-mono">{{ rtrim(rtrim(number_format((float) $line->exchange_rate, 10, '.', ''), '0'), '.') }}</td>
                    <td class="p-3">{{ $line->description }}</td>
                    <td class="p-3 text-center">{{ number_format((float) $line->debit, 2) }}</td>
                    <td class="p-3 text-center">{{ number_format((float) $line->credit, 2) }}</td>
                    <td class="p-3 text-center">{{ number_format((float) $line->debit_base, 2) }}</td>
                    <td class="p-3 text-center">{{ number_format((float) $line->credit_base, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot class="bg-slate-50 font-bold">
            <tr>
                <td colspan="6" class="p-3 text-end">{{ __('Base totals (USD)') }}</td>
                <td class="p-3 text-center">{{ number_format((float) $journal->total_debit, 2) }}</td>
                <td class="p-3 text-center">{{ number_format((float) $journal->total_credit, 2) }}</td>
            </tr>
        </tfoot>
    </table>
</div>

<div class="mt-6 rounded-2xl border bg-white p-5">
    <h2 class="mb-3 text-lg font-semibold">{{ __('Attachments') }}</h2>
    @forelse($journal->attachments as $attachment)
        <div class="flex items-center justify-between border-b border-slate-100 py-2 text-sm last:border-0">
            <span>{{ $attachment->original_name }} <span class="text-slate-400">({{ number_format(($attachment->file_size ?? 0) / 1024, 1) }} KB)</span></span>
            <a class="text-brand-navy underline" href="{{ route('admin.journals.attachments.download', [$journal, $attachment]) }}">{{ __('Download') }}</a>
        </div>
    @empty
        <p class="text-sm text-slate-500">{{ __('No attachments.') }}</p>
    @endforelse
</div>
@endsection

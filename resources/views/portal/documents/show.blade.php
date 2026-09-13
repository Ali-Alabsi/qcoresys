@extends('layouts.portal')
@section('title', $kind === 'quotation' ? $record->quotation_no : $record->invoice_no)
@section('content')
<div class="mb-6 flex items-center justify-between"><div><h1 class="text-2xl font-bold">{{ $kind === 'quotation' ? $record->quotation_no : $record->invoice_no }}</h1><p class="text-sm text-slate-500">{{ $record->status->value }}</p></div><div class="flex flex-wrap gap-2"><a target="_blank" class="btn-secondary !px-4 !py-2" href="{{ route('portal.'.$kind.'s.pdf',$record) }}?preview=1">{{ __('Preview') }}</a><a target="_blank" class="btn-secondary !px-4 !py-2" href="{{ route('portal.'.$kind.'s.pdf',$record) }}?print=1">{{ __('Print') }}</a><a class="btn-primary !px-4 !py-2" href="{{ route('portal.'.$kind.'s.pdf',$record) }}">{{ __('Export PDF') }}</a></div></div>
<div class="overflow-x-auto rounded-2xl border bg-white"><table class="min-w-full text-sm"><thead class="bg-slate-50"><tr><th class="p-3 text-start">{{ __('Description') }}</th><th>{{ __('Price') }}</th><th>{{ __('Total') }}</th></tr></thead>
<tbody class="divide-y">@foreach($record->items as $item)<tr><td class="p-3">{{ $item->description }}</td><td class="text-center">{{ number_format((float)$item->unit_price,2) }}</td><td class="text-center">{{ number_format((float)($item->total_amount ?? $item->total),2) }}</td></tr>@endforeach</tbody>
<tfoot class="bg-slate-50 font-bold"><tr><td colspan="2" class="p-3 text-end">{{ __('Total') }}</td><td class="text-center">{{ $record->currency->code }} {{ number_format((float)$record->total_amount,2) }}</td></tr></tfoot></table></div>
@endsection

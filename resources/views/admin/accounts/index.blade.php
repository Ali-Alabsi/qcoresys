@extends('layouts.admin')
@section('title', __('Chart of accounts'))
@section('content')
<h1 class="mb-6 text-2xl font-bold">{{ __('Chart of accounts') }}</h1>
<div class="mb-4 grid grid-cols-3 gap-3 text-xs font-semibold uppercase text-slate-500 sm:grid-cols-[1fr_auto_auto]">
    <div>{{ __('Account') }}</div>
    <div class="hidden sm:block">{{ __('Type') }}</div>
    <div class="text-end">{{ __('Balance') }}</div>
</div>
<div class="rounded-2xl border border-slate-200 bg-white p-5">
    @php
        $render = function ($nodes, $depth = 0) use (&$render) {
            foreach ($nodes as $node) {
                $typeLabel = __('account_type.'.$node->account_type->value);
                echo '<div class="grid grid-cols-1 gap-1 border-b border-slate-100 py-2 text-sm sm:grid-cols-[1fr_auto_auto] sm:items-center" style="padding-inline-start:'.($depth * 24).'px">';
                echo '<span><span class="font-mono text-slate-500">'.e($node->account_code).'</span> <span class="ms-3 font-medium">'.e($node->localized_name).'</span></span>';
                echo '<span class="text-slate-500">'.e($typeLabel).'</span>';
                echo '<span class="text-end text-slate-600">'.e(number_format((float) $node->current_balance, 2)).'</span></div>';
                $node->loadMissing('children');
                $render($node->children->sortBy('account_code'), $depth + 1);
            }
        };
        $render($accounts);
    @endphp
</div>
@endsection

@extends('layouts.admin')
@section('title', $title)
@section('content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-4">
    <h1 class="text-2xl font-bold">{{ $title }}</h1>
    @php
        $routeModule = str_replace('admin.', '', $routeBase);
        $permissionModule = ['portfolio-projects' => 'portfolio', 'services' => 'services_catalog'][$routeModule] ?? str_replace('-', '_', $routeModule);
    @endphp
    <div class="flex flex-wrap items-center gap-3">
        @include('admin.shared.per-page', ['paginator' => $records])
        @if(Route::has($routeBase.'.create'))
            @if(auth()->user()->hasPermission($permissionModule.'.create'))
                <a class="btn-primary !px-4 !py-2" href="{{ route($routeBase.'.create') }}">{{ __('Create') }}</a>
            @endif
        @endif
    </div>
</div>
<div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white">
    <table class="min-w-full text-sm">
        <thead class="bg-slate-50 text-start text-xs uppercase text-slate-500"><tr>@foreach($columns as $label)<th class="px-4 py-3 text-start">{{ $label }}</th>@endforeach<th class="px-4 py-3"></th></tr></thead>
        <tbody class="divide-y divide-slate-100">
        @forelse($records as $record)
            <tr class="hover:bg-slate-50">
                @foreach($columns as $key => $label)
                    @php
                        $value = data_get($record, $key);
                        if ($value instanceof \BackedEnum) {
                            $enumKey = 'enums.'.$value->value;
                            $translated = __($enumKey);
                            $value = $translated === $enumKey ? \Illuminate\Support\Str::headline($value->value) : $translated;
                        }
                    @endphp
                    <td class="whitespace-nowrap px-4 py-3">{{ is_bool($value) ? ($value ? __('Yes') : __('No')) : $value }}</td>
                @endforeach
                <td class="whitespace-nowrap px-4 py-3">
                    @php
                        $status = $record->status ?? null;
                        $canEditRecord = true;
                        $canApproveRecord = true;
                        $canPostRecord = true;
                        if ($status instanceof \App\Enums\QuotationStatus) {
                            $canEditRecord = in_array($status, [\App\Enums\QuotationStatus::Draft, \App\Enums\QuotationStatus::InReview], true);
                            $canApproveRecord = in_array($status, [\App\Enums\QuotationStatus::Draft, \App\Enums\QuotationStatus::InReview], true);
                            $canPostRecord = false;
                        } elseif ($status instanceof \App\Enums\InvoiceStatus) {
                            $canEditRecord = $status === \App\Enums\InvoiceStatus::Draft;
                            $canApproveRecord = $status === \App\Enums\InvoiceStatus::Draft;
                            $canPostRecord = $status === \App\Enums\InvoiceStatus::Approved;
                        } elseif ($status instanceof \App\Enums\ExpenseStatus) {
                            $canEditRecord = $status === \App\Enums\ExpenseStatus::Draft;
                            $canApproveRecord = $status === \App\Enums\ExpenseStatus::Draft;
                            $canPostRecord = $status === \App\Enums\ExpenseStatus::Approved;
                        }
                    @endphp
                    <div class="flex flex-row flex-nowrap items-center justify-end gap-3">
                        @if($show && Route::has($routeBase.'.show'))
                            <a class="font-semibold text-cyan-600" href="{{ route($routeBase.'.show', $record) }}">{{ __('View') }}</a>
                        @endif
                        @if($canEditRecord && Route::has($routeBase.'.edit') && auth()->user()->hasPermission($permissionModule.'.update'))
                            <a class="font-semibold text-cyan-600" href="{{ route($routeBase.'.edit', $record) }}">{{ __('Edit') }}</a>
                        @endif
                        @if($canApproveRecord && Route::has($routeBase.'.approve') && auth()->user()->hasPermission($permissionModule.'.approve'))
                            <form class="inline" method="POST" action="{{ route($routeBase.'.approve', $record) }}">@csrf<button type="submit" class="font-semibold text-cyan-600">{{ __('Approve') }}</button></form>
                        @endif
                        @if($canPostRecord && Route::has($routeBase.'.post') && auth()->user()->hasPermission($permissionModule.'.post'))
                            <form class="inline-flex flex-col items-end gap-1" method="POST" action="{{ route($routeBase.'.post', $record) }}" enctype="multipart/form-data">
                                @csrf
                                <input type="file" name="attachments[]" accept=".pdf,.png,.jpg,.jpeg" multiple required class="max-w-[10rem] text-[10px]" title="{{ __('Supporting documents') }}">
                                <button type="submit" class="font-semibold text-brand-navy">{{ __('Post') }}</button>
                            </form>
                        @endif
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="{{ count($columns) + 1 }}" class="px-4 py-10 text-center text-slate-500">{{ __('No records found.') }}</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-5">@include('admin.shared.pagination', ['paginator' => $records])</div>
@endsection

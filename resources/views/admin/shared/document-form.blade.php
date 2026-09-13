@extends('layouts.admin')
@section('title', $title)
@section('content')
@php
    $formConfig = [
        'items' => old('items', $initialItems),
        'discount_amount' => (float) old('discount_amount', $record?->discount_amount ?? 0),
        'other_amount' => (float) old('other_amount', $record?->other_amount ?? 0),
    ];
@endphp
<h1 class="mb-6 text-2xl font-bold">{{ $title }}</h1>
<form method="POST" action="{{ $action }}" class="rounded-2xl border border-slate-200 bg-white p-6"
      x-data="documentLineForm({{ \Illuminate\Support\Js::from($formConfig) }})">
    @csrf
    @if($method !== 'POST') @method($method) @endif

    <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div>
            <label class="label-public" for="customer_id">{{ __('Customer') }}</label>
            <select class="input-public" id="customer_id" name="customer_id" @disabled($record) required>
                <option value="">{{ __('Select') }}</option>
                @foreach($customers as $id => $name)
                    <option value="{{ $id }}" @selected((string) old('customer_id', $record?->customer_id ?? '') === (string) $id)>{{ $name }}</option>
                @endforeach
            </select>
            @if($record)<input type="hidden" name="customer_id" value="{{ $record->customer_id }}">@endif
            @error('customer_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="label-public" for="{{ $dateField }}">{{ __('Date') }}</label>
            <input class="input-public" id="{{ $dateField }}" name="{{ $dateField }}" type="date"
                   value="{{ old($dateField, optional($record?->{$dateField})?->format('Y-m-d') ?? now()->toDateString()) }}" required>
            @error($dateField)<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="label-public" for="{{ $untilField }}">{{ $untilLabel }}</label>
            <input class="input-public" id="{{ $untilField }}" name="{{ $untilField }}" type="date"
                   value="{{ old($untilField, optional($record?->{$untilField})?->format('Y-m-d') ?? now()->addDays(30)->toDateString()) }}">
            @error($untilField)<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="label-public" for="currency_id">{{ __('Currency') }}</label>
            <select class="input-public" id="currency_id" name="currency_id" @disabled($record) required>
                <option value="">{{ __('Select') }}</option>
                @foreach($currencies as $id => $code)
                    <option value="{{ $id }}" @selected((string) old('currency_id', $record?->currency_id ?? '') === (string) $id)>{{ $code }}</option>
                @endforeach
            </select>
            @if($record)<input type="hidden" name="currency_id" value="{{ $record->currency_id }}">@endif
            @error('currency_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="label-public" for="discount_amount">{{ __('Document discount') }}</label>
            <input class="input-public" id="discount_amount" name="discount_amount" type="number" step="0.01" min="0" x-model.number="discount_amount">
            @error('discount_amount')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="label-public" for="other_amount">{{ __('Other amount') }}</label>
            <input class="input-public" id="other_amount" name="other_amount" type="number" step="0.01" x-model.number="other_amount">
            @error('other_amount')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div class="sm:col-span-2 lg:col-span-3">
            <label class="label-public" for="notes">{{ __('Notes') }}</label>
            <textarea class="input-public" id="notes" name="notes" rows="2">{{ old('notes', $record?->notes ?? '') }}</textarea>
        </div>
        <div class="sm:col-span-2 lg:col-span-3">
            <label class="label-public" for="{{ $termsField }}">{{ $termsLabel }}</label>
            <textarea class="input-public" id="{{ $termsField }}" name="{{ $termsField }}" rows="3">{{ old($termsField, $record?->{$termsField} ?? '') }}</textarea>
        </div>
    </div>

    <div class="mb-3 flex items-center justify-between">
        <h2 class="text-lg font-semibold text-slate-800">{{ __('Line items') }}</h2>
        <button type="button" class="btn-secondary !px-3 !py-1.5" @click="addItem()">{{ __('Add line') }}</button>
    </div>

    <div class="overflow-x-auto rounded-xl border border-slate-200">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="p-2 text-start">{{ __('Description') }}</th>
                    <th class="p-2 text-start">{{ __('Price') }}</th>
                    <th class="p-2 text-start">{{ __('Discount %') }}</th>
                    <th class="p-2 text-start">{{ __('Tax %') }}</th>
                    <th class="p-2 text-start">{{ __('Line total') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <template x-for="(item, index) in items" :key="index">
                    <tr class="border-t border-slate-100">
                        <td class="p-2"><input class="input-public" :name="`items[${index}][description]`" x-model="item.description" required></td>
                        <td class="p-2">
                            <input type="hidden" :name="`items[${index}][quantity]`" value="1">
                            <input class="input-public w-28" type="number" step="0.01" min="0" :name="`items[${index}][unit_price]`" x-model.number="item.unit_price" required>
                        </td>
                        <td class="p-2"><input class="input-public w-24" type="number" step="0.01" min="0" :name="`items[${index}][discount_percentage]`" x-model.number="item.discount_percentage"></td>
                        <td class="p-2"><input class="input-public w-24" type="number" step="0.01" min="0" :name="`items[${index}][tax_percentage]`" x-model.number="item.tax_percentage"></td>
                        <td class="p-2 font-mono text-xs" x-text="formatMoney(lineTotal(item))"></td>
                        <td class="p-2"><button type="button" class="text-red-600" @click="removeItem(index)" x-show="items.length > 1">✕</button></td>
                    </tr>
                </template>
            </tbody>
            <tfoot class="bg-slate-50 font-semibold">
                <tr>
                    <td colspan="4" class="p-2 text-end">{{ __('Subtotal') }}</td>
                    <td class="p-2 font-mono text-xs" x-text="formatMoney(subtotal())"></td>
                    <td></td>
                </tr>
                <tr>
                    <td colspan="4" class="p-2 text-end">{{ __('Total') }}</td>
                    <td class="p-2 font-mono text-xs" x-text="formatMoney(grandTotal())"></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
    @error('items')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
    @error('items.0.description')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror

    <div class="mt-6 flex gap-3">
        <button class="btn-primary">{{ __('Save') }}</button>
        <button type="button" class="btn-secondary" onclick="history.back()">{{ __('Cancel') }}</button>
    </div>
</form>
@endsection

@push('scripts')
<script>
function documentLineForm(config) {
    const blank = () => ({ description: '', unit_price: 0, discount_percentage: 0, tax_percentage: 0 });
    return {
        items: (config.items && config.items.length) ? config.items.map((item) => ({
            description: item.description || '',
            unit_price: Number(item.unit_price ?? 0),
            discount_percentage: Number(item.discount_percentage ?? 0),
            tax_percentage: Number(item.tax_percentage ?? 0),
        })) : [blank()],
        discount_amount: Number(config.discount_amount || 0),
        other_amount: Number(config.other_amount || 0),
        addItem() { this.items.push(blank()); },
        removeItem(index) { if (this.items.length > 1) this.items.splice(index, 1); },
        lineTotal(item) {
            const gross = Number(item.unit_price || 0);
            const discount = gross * (Number(item.discount_percentage || 0) / 100);
            const sub = gross - discount;
            const tax = sub * (Number(item.tax_percentage || 0) / 100);
            return sub + tax;
        },
        subtotal() { return this.items.reduce((sum, item) => sum + this.lineTotal(item), 0); },
        grandTotal() { return this.subtotal() - Number(this.discount_amount || 0) + Number(this.other_amount || 0); },
        formatMoney(value) { return Number(value || 0).toFixed(2); },
    };
}
</script>
@endpush

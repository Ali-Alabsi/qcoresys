@extends('layouts.admin')
@section('title', __('New journal entry'))
@section('content')
@php
    $journalFormConfig = [
        'accounts' => $accountCurrencies,
        'baseCurrencyId' => (int) $currency->id,
        'initialDate' => old('entry_date', now()->toDateString()),
    ];
@endphp
<h1 class="mb-6 text-2xl font-bold">{{ __('New journal entry') }}</h1>
<form method="POST" action="{{ route('admin.journals.store') }}" enctype="multipart/form-data" class="rounded-2xl border border-slate-200 bg-white p-6"
      x-data="journalForm({{ \Illuminate\Support\Js::from($journalFormConfig) }})">
    @csrf
    <input type="hidden" name="currency_id" value="{{ $currency->id }}">
    <div class="mb-6 grid gap-4 sm:grid-cols-2">
        <div>
            <label class="label-public">{{ __('Date') }}</label>
            <input class="input-public" type="date" name="entry_date" x-model="entryDate" required>
        </div>
        <div>
            <label class="label-public">{{ __('Description') }}</label>
            <input class="input-public" name="description" value="{{ old('description') }}" required>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
                <tr>
                    <th class="p-2 text-start">{{ __('Account') }}</th>
                    <th class="p-2 text-start">{{ __('Currency') }}</th>
                    <th class="p-2 text-start">{{ __('Description') }}</th>
                    <th class="p-2 text-start">{{ __('Debit') }}</th>
                    <th class="p-2 text-start">{{ __('Credit') }}</th>
                    <th class="p-2 text-start">{{ __('Base') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <template x-for="(line, index) in lines" :key="index">
                    <tr>
                        <td class="p-2">
                            <select class="input-public min-w-56" :name="`lines[${index}][account_id]`" x-model="line.account_id" @change="onAccountChange(line)" required>
                                <option value="">{{ __('Select') }}</option>
                                @foreach($accounts as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                            <input type="hidden" :name="`lines[${index}][currency_id]`" :value="line.currency_id">
                            <input type="hidden" :name="`lines[${index}][exchange_rate]`" :value="line.exchange_rate">
                        </td>
                        <td class="p-2"><span class="font-mono text-slate-600" x-text="line.currency_code || '-'"></span></td>
                        <td class="p-2"><input class="input-public" :name="`lines[${index}][description]`" x-model="line.description"></td>
                        <td class="p-2"><input class="input-public w-28" type="number" step="0.01" min="0" :name="`lines[${index}][debit]`" x-model="line.debit" @input="onAmountInput(line, 'debit')"></td>
                        <td class="p-2"><input class="input-public w-28" type="number" step="0.01" min="0" :name="`lines[${index}][credit]`" x-model="line.credit" @input="onAmountInput(line, 'credit')"></td>
                        <td class="p-2 font-mono text-xs text-slate-500" x-text="baseAmount(line)"></td>
                        <td class="p-2"><button type="button" class="text-red-600" @click="removeLine(index)">✕</button></td>
                    </tr>
                </template>
            </tbody>
            <tfoot class="bg-slate-50 font-semibold">
                <tr>
                    <td colspan="5" class="p-2 text-end">{{ __('Base totals (USD)') }}</td>
                    <td class="p-2 font-mono text-xs" colspan="2">
                        <span x-text="totalDebitBase()"></span> / <span x-text="totalCreditBase()"></span>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
    <div class="mt-6">
        <label class="label-public" for="attachments">{{ __('Attachments') }}</label>
        <input id="attachments" class="input-public" type="file" name="attachments[]" multiple accept=".pdf,.doc,.docx,.png,.jpg,.jpeg,.zip">
        <p class="mt-1 text-xs text-slate-500">{{ __('Optional. Up to 5 files (PDF, Word, images, ZIP). Max 10MB each.') }}</p>
    </div>
    <div class="mt-5 flex gap-3">
        <button type="button" class="btn-secondary" @click="addLine()">{{ __('Add line') }}</button>
        <button class="btn-primary">{{ __('Save') }}</button>
    </div>
</form>
<script>
function journalForm(config) {
    const emptyLine = () => ({
        account_id: '',
        currency_id: config.baseCurrencyId,
        currency_code: 'USD',
        exchange_rate: 1,
        description: '',
        debit: 0,
        credit: 0,
        amountManual: false,
    });

    return {
        accounts: config.accounts,
        baseCurrencyId: config.baseCurrencyId,
        entryDate: config.initialDate,
        lines: [emptyLine(), emptyLine()],
        syncing: false,
        addLine() { this.lines.push(emptyLine()); },
        removeLine(index) { if (this.lines.length > 2) this.lines.splice(index, 1); },
        onAccountChange(line) {
            const meta = this.accounts[line.account_id];
            if (!meta) return;
            line.currency_id = meta.currency_id;
            line.currency_code = meta.currency_code;
            line.exchange_rate = 1;
            this.syncCounterpart(line);
        },
        onAmountInput(line, side) {
            line.amountManual = true;
            if (side === 'debit' && Number(line.debit || 0) > 0) line.credit = 0;
            if (side === 'credit' && Number(line.credit || 0) > 0) line.debit = 0;
            this.syncCounterpart(line);
        },
        syncCounterpart(sourceLine) {
            if (this.syncing) return;
            const amount = Number(sourceLine.debit || 0) > 0
                ? Number(sourceLine.debit || 0)
                : Number(sourceLine.credit || 0);
            if (amount <= 0) return;

            const others = this.lines.filter((line) => line !== sourceLine);
            if (others.length !== 1) return;

            const other = others[0];
            if (other.amountManual && (Number(other.debit || 0) > 0 || Number(other.credit || 0) > 0)) {
                return;
            }

            const sourceIsDebit = Number(sourceLine.debit || 0) > 0;

            this.syncing = true;
            try {
                if (sourceIsDebit) {
                    other.debit = 0;
                    other.credit = amount;
                } else {
                    other.credit = 0;
                    other.debit = amount;
                }
                other.amountManual = false;
            } finally {
                this.syncing = false;
            }
        },
        baseAmount(line) {
            const debit = Number(line.debit || 0);
            const credit = Number(line.credit || 0);
            const amount = debit > 0 ? debit : credit;
            return amount.toFixed(2);
        },
        totalDebitBase() {
            return this.lines.reduce((sum, line) => sum + Number(line.debit || 0), 0).toFixed(2);
        },
        totalCreditBase() {
            return this.lines.reduce((sum, line) => sum + Number(line.credit || 0), 0).toFixed(2);
        },
    };
}
</script>
@endsection

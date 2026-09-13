<?php

namespace App\Services;

use App\Enums\AccountType;
use App\Exceptions\DomainException;
use App\Models\Account;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\Payment;
use App\Models\Setting;

class AccountingService
{
    public function __construct(
        protected JournalEntryService $journalEntryService,
    ) {}

    public function getSettingAccountId(string $key): int
    {
        $value = Setting::query()->where('key', $key)->value('value');

        if (! $value) {
            throw new DomainException("Accounting setting [{$key}] is not configured.");
        }

        return (int) $value;
    }

    public function postInvoice(Invoice $invoice, ?int $postedBy = null): JournalEntry
    {
        $arAccountId = $this->getSettingAccountId('account_ar');
        $revenueAccountId = $invoice->project_id
            ? $this->getSettingAccountId('account_revenue_project')
            : $this->getSettingAccountId('account_revenue_consulting');

        $amount = (float) $invoice->total_amount;

        $entry = $this->journalEntryService->create([
            'entry_date' => $invoice->invoice_date?->toDateString() ?? now()->toDateString(),
            'reference_type' => 'invoice',
            'reference_id' => $invoice->id,
            'description' => 'Invoice '.$invoice->invoice_no,
            'currency_id' => $invoice->currency_id,
            'exchange_rate' => $invoice->exchange_rate ?? 1,
            'created_by' => $postedBy,
        ], [
            [
                'account_id' => $arAccountId,
                'customer_id' => $invoice->customer_id,
                'project_id' => $invoice->project_id,
                'description' => 'Accounts receivable',
                'debit' => $amount,
                'credit' => 0,
            ],
            [
                'account_id' => $revenueAccountId,
                'customer_id' => $invoice->customer_id,
                'project_id' => $invoice->project_id,
                'description' => 'Revenue',
                'debit' => 0,
                'credit' => $amount,
            ],
        ], $postedBy);

        return $this->journalEntryService->post($entry, $postedBy);
    }

    public function postPayment(Payment $payment, ?int $postedBy = null): JournalEntry
    {
        $arAccountId = $this->getSettingAccountId('account_ar');
        $cashOrBankId = $payment->account_id
            ?: $this->getSettingAccountId('account_bank');

        $amount = (float) $payment->amount;

        $entry = $this->journalEntryService->create([
            'entry_date' => $payment->payment_date?->toDateString() ?? now()->toDateString(),
            'reference_type' => 'payment',
            'reference_id' => $payment->id,
            'description' => 'Payment '.$payment->payment_no,
            'currency_id' => $payment->currency_id,
            'exchange_rate' => $payment->exchange_rate ?? 1,
            'created_by' => $postedBy,
        ], [
            [
                'account_id' => $cashOrBankId,
                'customer_id' => $payment->customer_id,
                'description' => 'Cash/Bank receipt',
                'debit' => $amount,
                'credit' => 0,
            ],
            [
                'account_id' => $arAccountId,
                'customer_id' => $payment->customer_id,
                'description' => 'Accounts receivable clearance',
                'debit' => 0,
                'credit' => $amount,
            ],
        ], $postedBy);

        return $this->journalEntryService->post($entry, $postedBy);
    }

    public function postExpense(Expense $expense, ?int $postedBy = null): JournalEntry
    {
        $expenseAccountId = $expense->account_id
            ?: $this->getSettingAccountId('account_expense_default');
        $cashOrBankId = $this->getSettingAccountId('account_bank');
        $amount = (float) $expense->total_amount;

        $entry = $this->journalEntryService->create([
            'entry_date' => $expense->expense_date?->toDateString() ?? now()->toDateString(),
            'reference_type' => 'expense',
            'reference_id' => $expense->id,
            'description' => 'Expense '.$expense->expense_no,
            'currency_id' => $expense->currency_id,
            'exchange_rate' => $expense->exchange_rate ?? 1,
            'created_by' => $postedBy,
        ], [
            [
                'account_id' => $expenseAccountId,
                'customer_id' => $expense->customer_id,
                'vendor_id' => $expense->vendor_id,
                'project_id' => $expense->project_id,
                'description' => $expense->description,
                'debit' => $amount,
                'credit' => 0,
            ],
            [
                'account_id' => $cashOrBankId,
                'vendor_id' => $expense->vendor_id,
                'project_id' => $expense->project_id,
                'description' => 'Payment for expense',
                'debit' => 0,
                'credit' => $amount,
            ],
        ], $postedBy);

        return $this->journalEntryService->post($entry, $postedBy);
    }

    public function findAccountByCode(string $code): ?Account
    {
        return Account::query()->where('account_code', $code)->first();
    }

    public function revenueAccounts(): \Illuminate\Database\Eloquent\Collection
    {
        return Account::query()->where('account_type', AccountType::Revenue)->get();
    }

    public function expenseAccounts(): \Illuminate\Database\Eloquent\Collection
    {
        return Account::query()->where('account_type', AccountType::Expense)->get();
    }
}

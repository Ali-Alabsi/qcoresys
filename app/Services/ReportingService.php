<?php

namespace App\Services;

use App\Enums\AccountType;
use App\Enums\InvoiceStatus;
use App\Enums\JournalStatus;
use App\Enums\PaymentStatus;
use App\Enums\NormalBalance;
use App\Models\Account;
use App\Models\Invoice;
use App\Models\JournalEntryLine;
use App\Models\Payment;
use App\Support\Money;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReportingService
{
    public function revenueReport(?string $from = null, ?string $to = null, ?int $customerId = null): array
    {
        $query = JournalEntryLine::query()
            ->select([
                'accounts.id as account_id',
                'accounts.account_code',
                'accounts.account_name',
                'journal_entry_lines.customer_id',
                DB::raw('SUM(journal_entry_lines.credit_base - journal_entry_lines.debit_base) as amount'),
            ])
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->join('accounts', 'accounts.id', '=', 'journal_entry_lines.account_id')
            ->where('journal_entries.status', JournalStatus::Posted->value)
            ->where('accounts.account_type', AccountType::Revenue->value)
            ->groupBy('accounts.id', 'accounts.account_code', 'accounts.account_name', 'journal_entry_lines.customer_id');

        if ($from !== null) {
            $query->whereDate('journal_entries.entry_date', '>=', $from);
        }

        if ($to !== null) {
            $query->whereDate('journal_entries.entry_date', '<=', $to);
        }

        if ($customerId !== null) {
            $query->where('journal_entry_lines.customer_id', $customerId);
        }

        $rows = $query->get()->map(fn ($row) => [
            'account_id' => (int) $row->account_id,
            'account_code' => $row->account_code,
            'account_name' => $row->account_name,
            'customer_id' => $row->customer_id ? (int) $row->customer_id : null,
            'amount' => Money::round($row->amount),
        ])->all();

        $total = Money::round(collect($rows)->sum('amount'));

        return [
            'from' => $from,
            'to' => $to,
            'customer_id' => $customerId,
            'total' => $total,
            'lines' => $rows,
        ];
    }

    public function expenseReport(?string $from = null, ?string $to = null, ?int $projectId = null): array
    {
        $query = JournalEntryLine::query()
            ->select([
                'accounts.id as account_id',
                'accounts.account_code',
                'accounts.account_name',
                'journal_entry_lines.project_id',
                DB::raw('SUM(journal_entry_lines.debit_base - journal_entry_lines.credit_base) as amount'),
            ])
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->join('accounts', 'accounts.id', '=', 'journal_entry_lines.account_id')
            ->where('journal_entries.status', JournalStatus::Posted->value)
            ->where('accounts.account_type', AccountType::Expense->value)
            ->groupBy('accounts.id', 'accounts.account_code', 'accounts.account_name', 'journal_entry_lines.project_id');

        if ($from !== null) {
            $query->whereDate('journal_entries.entry_date', '>=', $from);
        }

        if ($to !== null) {
            $query->whereDate('journal_entries.entry_date', '<=', $to);
        }

        if ($projectId !== null) {
            $query->where('journal_entry_lines.project_id', $projectId);
        }

        $rows = $query->get()->map(fn ($row) => [
            'account_id' => (int) $row->account_id,
            'account_code' => $row->account_code,
            'account_name' => $row->account_name,
            'project_id' => $row->project_id ? (int) $row->project_id : null,
            'amount' => Money::round($row->amount),
        ])->all();

        $total = Money::round(collect($rows)->sum('amount'));

        return [
            'from' => $from,
            'to' => $to,
            'project_id' => $projectId,
            'total' => $total,
            'lines' => $rows,
        ];
    }

    public function trialBalance(?string $asOf = null): array
    {
        $asOfDate = $asOf ?? now()->toDateString();

        $accounts = Account::query()
            ->where('is_active', true)
            ->orderBy('account_code')
            ->get();

        $movementQuery = JournalEntryLine::query()
            ->select([
                'journal_entry_lines.account_id',
                DB::raw('SUM(journal_entry_lines.debit_base) as total_debit'),
                DB::raw('SUM(journal_entry_lines.credit_base) as total_credit'),
            ])
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->where('journal_entries.status', JournalStatus::Posted->value)
            ->whereDate('journal_entries.entry_date', '<=', $asOfDate)
            ->groupBy('journal_entry_lines.account_id');

        $movements = $movementQuery->get()->keyBy('account_id');

        $lines = [];
        $totalDebit = 0.0;
        $totalCredit = 0.0;

        foreach ($accounts as $account) {
            $movement = $movements->get($account->id);
            $debit = Money::round($movement->total_debit ?? 0);
            $credit = Money::round($movement->total_credit ?? 0);

            $openingDebit = (float) $account->opening_debit;
            $openingCredit = (float) $account->opening_credit;

            $debit = Money::add($debit, $openingDebit);
            $credit = Money::add($credit, $openingCredit);

            $balance = $account->normal_balance === NormalBalance::Debit
                ? Money::subtract($debit, $credit)
                : Money::subtract($credit, $debit);

            if (Money::isZero($debit) && Money::isZero($credit) && Money::isZero($balance)) {
                continue;
            }

            $lines[] = [
                'account_id' => $account->id,
                'account_code' => $account->account_code,
                'account_name' => $account->account_name,
                'account_type' => $account->account_type->value,
                'debit' => $debit,
                'credit' => $credit,
                'balance' => $balance,
            ];

            $totalDebit = Money::add($totalDebit, $debit);
            $totalCredit = Money::add($totalCredit, $credit);
        }

        return [
            'as_of' => $asOfDate,
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'is_balanced' => Money::equals($totalDebit, $totalCredit),
            'lines' => $lines,
        ];
    }

    public function generalLedger(int $accountId, ?string $from = null, ?string $to = null): array
    {
        return $this->buildGeneralLedger($accountId, $from, $to, null);
    }

    public function generalLedgerPaginated(int $accountId, ?string $from, ?string $to, int $perPage): array
    {
        return $this->buildGeneralLedger($accountId, $from, $to, max(1, $perPage));
    }

    /**
     * @return array{
     *     account_id:int,
     *     account_code:string,
     *     account_name:string,
     *     from:?string,
     *     to:?string,
     *     opening_balance:float,
     *     page_opening_balance:float,
     *     closing_balance:float,
     *     total_debit:float,
     *     total_credit:float,
     *     lines:list<array<string, mixed>>,
     *     paginator:?\Illuminate\Contracts\Pagination\LengthAwarePaginator
     * }
     */
    private function buildGeneralLedger(int $accountId, ?string $from, ?string $to, ?int $perPage): array
    {
        $account = Account::query()->findOrFail($accountId);

        $baseQuery = JournalEntryLine::query()
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->where('journal_entry_lines.account_id', $accountId)
            ->where('journal_entries.status', JournalStatus::Posted->value);

        if ($from !== null) {
            $baseQuery->whereDate('journal_entries.entry_date', '>=', $from);
        }

        if ($to !== null) {
            $baseQuery->whereDate('journal_entries.entry_date', '<=', $to);
        }

        if ($from !== null) {
            $openingAsOf = Carbon::parse($from)->subDay()->toDateString();
            $openingBalance = (float) app(AccountBalanceService::class)->balanceAsOf($account, $openingAsOf)['base'];
        } else {
            $openingBalance = $account->normal_balance === NormalBalance::Debit
                ? Money::subtract($account->opening_debit, $account->opening_credit)
                : Money::subtract($account->opening_credit, $account->opening_debit);
        }

        $totals = (clone $baseQuery)
            ->selectRaw('COALESCE(SUM(journal_entry_lines.debit_base), 0) as total_debit, COALESCE(SUM(journal_entry_lines.credit_base), 0) as total_credit')
            ->first();

        $totalDebit = (float) ($totals->total_debit ?? 0);
        $totalCredit = (float) ($totals->total_credit ?? 0);

        if ($account->normal_balance === NormalBalance::Debit) {
            $closingBalance = Money::add($openingBalance, $totalDebit, -$totalCredit);
        } else {
            $closingBalance = Money::add($openingBalance, $totalCredit, -$totalDebit);
        }

        $orderedQuery = (clone $baseQuery)
            ->with(['journalEntry'])
            ->orderBy('journal_entries.entry_date')
            ->orderBy('journal_entry_lines.id')
            ->select('journal_entry_lines.*');

        $paginator = null;
        $pageOpeningBalance = $openingBalance;

        if ($perPage !== null) {
            $total = (clone $baseQuery)->count('journal_entry_lines.id');
            $page = max(1, (int) request()->integer('page', 1));
            $offset = ($page - 1) * $perPage;

            if ($offset > 0) {
                $prior = (clone $baseQuery)
                    ->orderBy('journal_entries.entry_date')
                    ->orderBy('journal_entry_lines.id')
                    ->skip(0)
                    ->take($offset)
                    ->select([
                        'journal_entry_lines.debit_base',
                        'journal_entry_lines.credit_base',
                    ])
                    ->get();

                foreach ($prior as $line) {
                    $debit = (float) $line->debit_base;
                    $credit = (float) $line->credit_base;
                    if ($account->normal_balance === NormalBalance::Debit) {
                        $pageOpeningBalance = Money::add($pageOpeningBalance, $debit, -$credit);
                    } else {
                        $pageOpeningBalance = Money::add($pageOpeningBalance, $credit, -$debit);
                    }
                }
            }

            $pageLines = (clone $orderedQuery)->skip($offset)->take($perPage)->get();

            $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
                $pageLines,
                $total,
                $perPage,
                $page,
                [
                    'path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(),
                    'pageName' => 'page',
                ]
            );
            $paginator->withQueryString();

            $sourceLines = $pageLines;
            $runningBalance = $pageOpeningBalance;
        } else {
            $sourceLines = $orderedQuery->get();
            $runningBalance = $openingBalance;
        }

        $lines = [];
        foreach ($sourceLines as $line) {
            $debit = (float) $line->debit_base;
            $credit = (float) $line->credit_base;

            if ($account->normal_balance === NormalBalance::Debit) {
                $runningBalance = Money::add($runningBalance, $debit, -$credit);
            } else {
                $runningBalance = Money::add($runningBalance, $credit, -$debit);
            }

            $lines[] = [
                'line_id' => $line->id,
                'entry_id' => $line->journal_entry_id,
                'entry_no' => $line->journalEntry?->entry_no,
                'entry_date' => $line->journalEntry?->entry_date?->toDateString(),
                'description' => $line->description ?? $line->journalEntry?->description,
                'debit' => $debit,
                'credit' => $credit,
                'running_balance' => $runningBalance,
            ];
        }

        return [
            'account_id' => $account->id,
            'account_code' => $account->account_code,
            'account_name' => $account->localized_name,
            'from' => $from,
            'to' => $to,
            'opening_balance' => $openingBalance,
            'page_opening_balance' => $pageOpeningBalance,
            'closing_balance' => $closingBalance,
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'lines' => $lines,
            'paginator' => $paginator,
        ];
    }

    public function customerStatement(int $customerId, ?string $from = null, ?string $to = null): array
    {
        $invoiceQuery = Invoice::query()
            ->where('customer_id', $customerId)
            ->whereNotIn('status', [InvoiceStatus::Draft, InvoiceStatus::Cancelled, InvoiceStatus::Void])
            ->orderBy('invoice_date');

        if ($from !== null) {
            $invoiceQuery->whereDate('invoice_date', '>=', $from);
        }

        if ($to !== null) {
            $invoiceQuery->whereDate('invoice_date', '<=', $to);
        }

        $paymentQuery = Payment::query()
            ->where('customer_id', $customerId)
            ->where('status', PaymentStatus::Posted)
            ->orderBy('payment_date');

        if ($from !== null) {
            $paymentQuery->whereDate('payment_date', '>=', $from);
        }

        if ($to !== null) {
            $paymentQuery->whereDate('payment_date', '<=', $to);
        }

        $transactions = collect();

        foreach ($invoiceQuery->get() as $invoice) {
            $transactions->push([
                'date' => $invoice->invoice_date?->toDateString(),
                'type' => 'invoice',
                'reference' => $invoice->invoice_no,
                'description' => 'Invoice '.$invoice->invoice_no,
                'debit' => (float) $invoice->total_amount,
                'credit' => 0.0,
            ]);
        }

        foreach ($paymentQuery->get() as $payment) {
            $transactions->push([
                'date' => $payment->payment_date?->toDateString(),
                'type' => 'payment',
                'reference' => $payment->payment_no,
                'description' => 'Payment '.$payment->payment_no,
                'debit' => 0.0,
                'credit' => (float) $payment->amount,
            ]);
        }

        $sorted = $transactions->sortBy('date')->values();

        $runningBalance = 0.0;
        $lines = [];

        foreach ($sorted as $transaction) {
            $runningBalance = Money::add($runningBalance, $transaction['debit'], -$transaction['credit']);
            $lines[] = [
                ...$transaction,
                'balance' => $runningBalance,
            ];
        }

        $outstanding = Money::round(
            Invoice::query()
                ->where('customer_id', $customerId)
                ->where('remaining_amount', '>', 0)
                ->whereNotIn('status', [InvoiceStatus::Cancelled->value, InvoiceStatus::Void->value, InvoiceStatus::Draft->value])
                ->sum('remaining_amount')
        );

        return [
            'customer_id' => $customerId,
            'from' => $from,
            'to' => $to,
            'outstanding_balance' => $outstanding,
            'closing_balance' => $runningBalance,
            'lines' => $lines,
        ];
    }

    public function receivablesAging(?string $asOf = null, ?int $customerId = null): array
    {
        $asOfDate = Carbon::parse($asOf ?? now()->toDateString())->endOfDay();

        $query = Invoice::query()
            ->where('remaining_amount', '>', 0)
            ->whereNotIn('status', [
                InvoiceStatus::Draft->value,
                InvoiceStatus::Cancelled->value,
                InvoiceStatus::Void->value,
                InvoiceStatus::Paid->value,
            ])
            ->whereDate('invoice_date', '<=', $asOfDate->toDateString());

        if ($customerId !== null) {
            $query->where('customer_id', $customerId);
        }

        $buckets = [
            'current' => 0.0,
            '1_30' => 0.0,
            '31_60' => 0.0,
            '61_90' => 0.0,
            'over_90' => 0.0,
        ];

        $details = [];

        foreach ($query->get() as $invoice) {
            $dueDate = $invoice->due_date ?? $invoice->invoice_date;
            $daysPastDue = $dueDate ? Carbon::parse($dueDate)->diffInDays($asOfDate, false) : 0;
            $amount = (float) $invoice->remaining_amount;

            if ($daysPastDue <= 0) {
                $bucket = 'current';
            } elseif ($daysPastDue <= 30) {
                $bucket = '1_30';
            } elseif ($daysPastDue <= 60) {
                $bucket = '31_60';
            } elseif ($daysPastDue <= 90) {
                $bucket = '61_90';
            } else {
                $bucket = 'over_90';
            }

            $buckets[$bucket] = Money::add($buckets[$bucket], $amount);

            $details[] = [
                'invoice_id' => $invoice->id,
                'invoice_no' => $invoice->invoice_no,
                'customer_id' => $invoice->customer_id,
                'invoice_date' => $invoice->invoice_date?->toDateString(),
                'due_date' => $dueDate?->toDateString(),
                'days_past_due' => max($daysPastDue, 0),
                'remaining_amount' => $amount,
                'bucket' => $bucket,
            ];
        }

        return [
            'as_of' => $asOfDate->toDateString(),
            'customer_id' => $customerId,
            'total_outstanding' => Money::round(array_sum($buckets)),
            'buckets' => $buckets,
            'invoices' => $details,
        ];
    }
}

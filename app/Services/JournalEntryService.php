<?php

namespace App\Services;

use App\Enums\AuditAction;
use App\Enums\JournalStatus;
use App\Enums\NormalBalance;
use App\Exceptions\DomainException;
use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Support\Money;
use Illuminate\Support\Facades\DB;

class JournalEntryService
{
    public function __construct(
        protected AuditService $auditService,
        protected AccountBalanceService $balances,
    ) {}

    /**
     * @param  array<int, array<string, mixed>>  $lines
     */
    public function create(array $header, array $lines, ?int $createdBy = null): JournalEntry
    {
        return DB::transaction(function () use ($header, $lines, $createdBy) {
            if (count($lines) < 2) {
                throw new DomainException('A journal entry requires at least two lines.');
            }

            $totals = $this->calculateTotals($lines, (float) ($header['exchange_rate'] ?? 1));

            $this->assertLinesBalanced($lines, $header, $totals);
            $this->assertSufficientBalances($lines, (string) ($header['entry_date'] ?? now()->toDateString()));

            $entry = JournalEntry::create([
                ...$header,
                'total_debit' => $totals['debit_base'],
                'total_credit' => $totals['credit_base'],
                'is_balanced' => true,
                'status' => JournalStatus::Draft,
                'is_reversed' => false,
                'created_by' => $createdBy ?? ($header['created_by'] ?? null),
            ]);

            foreach ($lines as $index => $line) {
                $rate = (float) ($line['exchange_rate'] ?? $header['exchange_rate'] ?? 1);
                $debit = (float) ($line['debit'] ?? 0);
                $credit = (float) ($line['credit'] ?? 0);
                $computed = $this->lineBaseAmounts($line, $rate);

                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'line_no' => $line['line_no'] ?? ($index + 1),
                    'account_id' => $line['account_id'],
                    'customer_id' => $line['customer_id'] ?? null,
                    'vendor_id' => $line['vendor_id'] ?? null,
                    'project_id' => $line['project_id'] ?? null,
                    'service_id' => $line['service_id'] ?? null,
                    'description' => $line['description'] ?? null,
                    'currency_id' => $line['currency_id'] ?? $header['currency_id'],
                    'exchange_rate' => $rate,
                    'debit' => $debit,
                    'credit' => $credit,
                    'debit_base' => $computed['debit_base'],
                    'credit_base' => $computed['credit_base'],
                    'reference_no' => $line['reference_no'] ?? null,
                ]);
            }

            $this->auditService->logModelEvent($entry, AuditAction::Create);

            return $entry->fresh('lines');
        });
    }

    public function validate(JournalEntry $entry): bool
    {
        $entry->loadMissing('lines');

        $debitBase = (float) $entry->lines->sum(fn ($l) => (float) $l->debit_base);
        $creditBase = (float) $entry->lines->sum(fn ($l) => (float) $l->credit_base);

        return $this->amountsEqual($debitBase, $creditBase)
            && $entry->lines->count() >= 2;
    }

    public function post(JournalEntry $entry, ?int $postedBy = null): JournalEntry
    {
        return DB::transaction(function () use ($entry, $postedBy) {
            $entry = JournalEntry::query()->lockForUpdate()->findOrFail($entry->id);
            $entry->load('lines');

            if ($entry->status === JournalStatus::Posted) {
                throw new DomainException('Journal entry is already posted.');
            }

            if ($entry->status === JournalStatus::Reversed) {
                throw new DomainException('Reversed journal entries cannot be posted.');
            }

            if (! $this->validate($entry)) {
                throw new DomainException('Unbalanced journal entry cannot be posted. Debit must equal credit in base currency.');
            }

            $asOf = $entry->entry_date?->toDateString() ?? now()->toDateString();
            $linePayload = $entry->lines->map(fn (JournalEntryLine $line) => [
                'account_id' => $line->account_id,
                'debit' => $line->debit,
                'credit' => $line->credit,
            ])->all();

            $accountIds = $entry->lines->pluck('account_id')->unique()->filter()->all();
            if ($accountIds !== []) {
                Account::query()->whereIn('id', $accountIds)->orderBy('id')->lockForUpdate()->get();
            }

            $this->assertSufficientBalances($linePayload, $asOf);

            $debitBase = (float) $entry->lines->sum(fn ($l) => (float) $l->debit_base);
            $creditBase = (float) $entry->lines->sum(fn ($l) => (float) $l->credit_base);

            $entry->update([
                'status' => JournalStatus::Posted,
                'is_balanced' => true,
                'total_debit' => $debitBase,
                'total_credit' => $creditBase,
                'posting_date' => $entry->posting_date ?? now()->toDateString(),
                'posted_by' => $postedBy,
                'posted_at' => now(),
            ]);

            foreach ($accountIds as $accountId) {
                $account = Account::query()->find($accountId);
                if ($account) {
                    $this->balances->syncCurrentBalance($account, $asOf);
                }
            }

            $this->auditService->logModelEvent($entry->fresh(), AuditAction::JournalPost);

            return $entry->fresh('lines');
        });
    }

    public function reverse(JournalEntry $entry, ?int $createdBy = null, ?string $description = null): JournalEntry
    {
        return DB::transaction(function () use ($entry, $createdBy, $description) {
            $entry = JournalEntry::query()->lockForUpdate()->findOrFail($entry->id);
            $entry->load('lines');

            if ($entry->status !== JournalStatus::Posted) {
                throw new DomainException('Only posted journal entries can be reversed.');
            }

            if ($entry->is_reversed) {
                throw new DomainException('Journal entry is already reversed.');
            }

            $reversingLines = $entry->lines->map(function (JournalEntryLine $line) {
                return [
                    'account_id' => $line->account_id,
                    'customer_id' => $line->customer_id,
                    'vendor_id' => $line->vendor_id,
                    'project_id' => $line->project_id,
                    'service_id' => $line->service_id,
                    'description' => 'Reversal: '.($line->description ?? ''),
                    'currency_id' => $line->currency_id,
                    'exchange_rate' => $line->exchange_rate,
                    'debit' => $line->credit,
                    'credit' => $line->debit,
                    'reference_no' => $line->reference_no,
                ];
            })->all();

            $reversing = $this->create([
                'entry_date' => now()->toDateString(),
                'reference_type' => $entry->reference_type,
                'reference_id' => $entry->reference_id,
                'description' => $description ?? ('Reversal of '.$entry->entry_no),
                'currency_id' => $entry->currency_id,
                'exchange_rate' => $entry->exchange_rate,
                'reversed_entry_id' => $entry->id,
                'created_by' => $createdBy,
            ], $reversingLines, $createdBy);

            $reversing = $this->post($reversing, $createdBy);

            $entry->update([
                'is_reversed' => true,
                'status' => JournalStatus::Reversed,
            ]);

            $this->auditService->logModelEvent($entry->fresh(), AuditAction::JournalReverse);

            return $reversing;
        });
    }

    public function assertNotPosted(JournalEntry $entry): void
    {
        if ($entry->status === JournalStatus::Posted || $entry->status === JournalStatus::Reversed) {
            throw new DomainException('Posted or reversed journal entries cannot be modified.');
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $lines
     * @return array{debit: float, credit: float, debit_base: float, credit_base: float}
     */
    protected function calculateTotals(array $lines, float $defaultRate): array
    {
        $debit = 0.0;
        $credit = 0.0;
        $debitBase = 0.0;
        $creditBase = 0.0;

        foreach ($lines as $line) {
            $rate = (float) ($line['exchange_rate'] ?? $defaultRate);
            $d = (float) ($line['debit'] ?? 0);
            $c = (float) ($line['credit'] ?? 0);
            $computed = $this->lineBaseAmounts($line, $rate);
            $debit += $d;
            $credit += $c;
            $debitBase += $computed['debit_base'];
            $creditBase += $computed['credit_base'];
        }

        return [
            'debit' => round($debit, 2),
            'credit' => round($credit, 2),
            'debit_base' => round($debitBase, 2),
            'credit_base' => round($creditBase, 2),
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $lines
     * @param  array<string, mixed>  $header
     * @param  array{debit: float, credit: float, debit_base: float, credit_base: float}  $totals
     */
    protected function assertLinesBalanced(array $lines, array $header, array $totals): void
    {
        $currencyIds = collect($lines)
            ->map(fn (array $line) => (int) ($line['currency_id'] ?? $header['currency_id'] ?? 0))
            ->filter()
            ->unique()
            ->values();

        if ($currencyIds->count() <= 1) {
            if (! $this->amountsEqual($totals['debit'], $totals['credit'])) {
                throw new DomainException(__('Journal entry is not balanced. Debit and credit amounts must be equal for the same currency.'));
            }
        }

        if (! $this->amountsEqual($totals['debit_base'], $totals['credit_base'])) {
            throw new DomainException(__('Journal entry is not balanced. Debit and credit must match after currency conversion.'));
        }
    }

    /**
     * Reject entries that would overdraw any account (all CoA accounts).
     *
     * @param  array<int, array<string, mixed>>  $lines
     */
    protected function assertSufficientBalances(array $lines, string $asOf): void
    {
        $grouped = [];

        foreach ($lines as $line) {
            $accountId = (int) ($line['account_id'] ?? 0);
            if ($accountId < 1) {
                continue;
            }

            $grouped[$accountId] ??= ['debit' => 0.0, 'credit' => 0.0];
            $grouped[$accountId]['debit'] = Money::add($grouped[$accountId]['debit'], $line['debit'] ?? 0);
            $grouped[$accountId]['credit'] = Money::add($grouped[$accountId]['credit'], $line['credit'] ?? 0);
        }

        if ($grouped === []) {
            return;
        }

        $accounts = Account::query()->whereIn('id', array_keys($grouped))->get()->keyBy('id');

        foreach ($grouped as $accountId => $movement) {
            /** @var Account|null $account */
            $account = $accounts->get($accountId);
            if (! $account) {
                throw new DomainException(__('Account not found for journal line.'));
            }

            $reduction = $account->normal_balance === NormalBalance::Debit
                ? Money::subtract($movement['credit'], $movement['debit'])
                : Money::subtract($movement['debit'], $movement['credit']);

            if ($reduction <= 0.005) {
                continue;
            }

            $available = $this->balances->balanceAsOf($account, $asOf)['foreign'];

            if ($reduction > $available + 0.005) {
                throw new DomainException(__('Insufficient balance for account :code. Available: :available, required: :required.', [
                    'code' => $account->account_code,
                    'available' => number_format($available, 2, '.', ''),
                    'required' => number_format($reduction, 2, '.', ''),
                ]));
            }
        }
    }

    /**
     * @param  array<string, mixed>  $line
     * @return array{debit_base: float, credit_base: float}
     */
    protected function lineBaseAmounts(array $line, float $rate): array
    {
        $debit = (float) ($line['debit'] ?? 0);
        $credit = (float) ($line['credit'] ?? 0);

        return [
            'debit_base' => array_key_exists('debit_base', $line)
                ? round((float) $line['debit_base'], 2)
                : round($debit * $rate, 2),
            'credit_base' => array_key_exists('credit_base', $line)
                ? round((float) $line['credit_base'], 2)
                : round($credit * $rate, 2),
        ];
    }

    protected function amountsEqual(float $a, float $b): bool
    {
        return abs($a - $b) < 0.005;
    }
}

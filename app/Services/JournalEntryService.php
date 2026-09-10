<?php

namespace App\Services;

use App\Enums\AuditAction;
use App\Enums\JournalStatus;
use App\Exceptions\DomainException;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class JournalEntryService
{
    public function __construct(
        protected AuditService $auditService,
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

            $entry = JournalEntry::create([
                ...$header,
                'total_debit' => $totals['debit'],
                'total_credit' => $totals['credit'],
                'is_balanced' => $this->amountsEqual($totals['debit'], $totals['credit'])
                    && $this->amountsEqual($totals['debit_base'], $totals['credit_base']),
                'status' => JournalStatus::Draft,
                'is_reversed' => false,
                'created_by' => $createdBy ?? ($header['created_by'] ?? null),
            ]);

            foreach ($lines as $index => $line) {
                $rate = (float) ($line['exchange_rate'] ?? $header['exchange_rate'] ?? 1);
                $debit = (float) ($line['debit'] ?? 0);
                $credit = (float) ($line['credit'] ?? 0);

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
                    'debit_base' => round($debit * $rate, 2),
                    'credit_base' => round($credit * $rate, 2),
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

        $debit = (float) $entry->lines->sum(fn ($l) => (float) $l->debit);
        $credit = (float) $entry->lines->sum(fn ($l) => (float) $l->credit);
        $debitBase = (float) $entry->lines->sum(fn ($l) => (float) $l->debit_base);
        $creditBase = (float) $entry->lines->sum(fn ($l) => (float) $l->credit_base);

        return $this->amountsEqual($debit, $credit)
            && $this->amountsEqual($debitBase, $creditBase)
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
                throw new DomainException('Unbalanced journal entry cannot be posted. Debit must equal credit.');
            }

            $debit = (float) $entry->lines->sum(fn ($l) => (float) $l->debit);
            $credit = (float) $entry->lines->sum(fn ($l) => (float) $l->credit);

            $entry->update([
                'status' => JournalStatus::Posted,
                'is_balanced' => true,
                'total_debit' => $debit,
                'total_credit' => $credit,
                'posting_date' => $entry->posting_date ?? now()->toDateString(),
                'posted_by' => $postedBy,
                'posted_at' => now(),
            ]);

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
            $debit += $d;
            $credit += $c;
            $debitBase += round($d * $rate, 2);
            $creditBase += round($c * $rate, 2);
        }

        return [
            'debit' => round($debit, 2),
            'credit' => round($credit, 2),
            'debit_base' => round($debitBase, 2),
            'credit_base' => round($creditBase, 2),
        ];
    }

    protected function amountsEqual(float $a, float $b): bool
    {
        return abs($a - $b) < 0.005;
    }
}

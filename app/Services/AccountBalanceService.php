<?php

namespace App\Services;

use App\Enums\JournalStatus;
use App\Enums\NormalBalance;
use App\Models\Account;
use App\Models\JournalEntryLine;
use App\Support\Money;

class AccountBalanceService
{
    /**
     * Posted ledger balance as of a date (openings + posted lines).
     *
     * @return array{foreign: float, base: float}
     */
    public function balanceAsOf(Account $account, string $asOf): array
    {
        $row = JournalEntryLine::query()
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->where('journal_entry_lines.account_id', $account->id)
            ->where('journal_entries.status', JournalStatus::Posted->value)
            ->whereDate('journal_entries.entry_date', '<=', $asOf)
            ->selectRaw('
                COALESCE(SUM(journal_entry_lines.debit), 0) as total_debit,
                COALESCE(SUM(journal_entry_lines.credit), 0) as total_credit,
                COALESCE(SUM(journal_entry_lines.debit_base), 0) as total_debit_base,
                COALESCE(SUM(journal_entry_lines.credit_base), 0) as total_credit_base
            ')
            ->first();

        $debit = Money::add($account->opening_debit, $row->total_debit ?? 0);
        $credit = Money::add($account->opening_credit, $row->total_credit ?? 0);
        $debitBase = Money::add($account->opening_debit, $row->total_debit_base ?? 0);
        $creditBase = Money::add($account->opening_credit, $row->total_credit_base ?? 0);

        if ($account->normal_balance === NormalBalance::Debit) {
            return [
                'foreign' => Money::subtract($debit, $credit),
                'base' => Money::subtract($debitBase, $creditBase),
            ];
        }

        return [
            'foreign' => Money::subtract($credit, $debit),
            'base' => Money::subtract($creditBase, $debitBase),
        ];
    }

    public function syncCurrentBalance(Account $account, ?string $asOf = null): Account
    {
        $balances = $this->balanceAsOf($account, $asOf ?? now()->toDateString());
        $account->forceFill(['current_balance' => $balances['foreign']])->save();

        return $account->fresh();
    }
}

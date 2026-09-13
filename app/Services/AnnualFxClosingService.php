<?php

namespace App\Services;

use App\Exceptions\DomainException;
use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\Setting;
use App\Support\Money;

class AnnualFxClosingService
{
    public function __construct(
        protected ExchangeRateService $exchangeRates,
        protected JournalEntryService $journals,
        protected AccountBalanceService $balances,
    ) {}

    public function createDraft(int $year, ?int $createdBy = null): JournalEntry
    {
        if ($year < 2000 || $year > 2100) {
            throw new DomainException(__('Invalid closing year.'));
        }

        $base = $this->exchangeRates->baseCurrency();
        $asOf = sprintf('%d-12-31', $year);

        $gainAccountId = (int) (Setting::query()->byKey('account_fx_gain')->value('value') ?? 0);
        $lossAccountId = (int) (Setting::query()->byKey('account_fx_loss')->value('value') ?? 0);

        if ($gainAccountId < 1 || $lossAccountId < 1) {
            throw new DomainException(__('FX gain/loss accounts are not configured.'));
        }

        $accounts = Account::query()
            ->postable()
            ->where(fn ($q) => $q->where('is_cash_account', true)->orWhere('is_bank_account', true))
            ->whereNotNull('currency_id')
            ->where('currency_id', '!=', $base->id)
            ->orderBy('account_code')
            ->get();

        $lines = [];
        $totalGain = 0.0;
        $totalLoss = 0.0;

        foreach ($accounts as $account) {
            $balances = $this->balances->balanceAsOf($account, $asOf);
            if (Money::isZero($balances['foreign'])) {
                continue;
            }

            $closingRate = $this->exchangeRates->closingRate((int) $account->currency_id, $base->id, $year);
            $revaluedBase = Money::round($balances['foreign'] * $closingRate);
            $diff = Money::subtract($revaluedBase, $balances['base']);

            if (Money::isZero($diff)) {
                continue;
            }

            if ($diff > 0) {
                $lines[] = [
                    'account_id' => $account->id,
                    'currency_id' => $account->currency_id,
                    'exchange_rate' => $closingRate,
                    'debit' => 0,
                    'credit' => 0,
                    'debit_base' => $diff,
                    'credit_base' => 0,
                    'description' => __('Annual FX revaluation :year', ['year' => $year]),
                ];
                $totalGain = Money::add($totalGain, $diff);
            } else {
                $loss = abs($diff);
                $lines[] = [
                    'account_id' => $account->id,
                    'currency_id' => $account->currency_id,
                    'exchange_rate' => $closingRate,
                    'debit' => 0,
                    'credit' => 0,
                    'debit_base' => 0,
                    'credit_base' => $loss,
                    'description' => __('Annual FX revaluation :year', ['year' => $year]),
                ];
                $totalLoss = Money::add($totalLoss, $loss);
            }
        }

        if ($totalGain > 0) {
            $lines[] = [
                'account_id' => $gainAccountId,
                'currency_id' => $base->id,
                'exchange_rate' => 1,
                'debit' => 0,
                'credit' => $totalGain,
                'description' => __('Foreign exchange gain :year', ['year' => $year]),
            ];
        }

        if ($totalLoss > 0) {
            $lines[] = [
                'account_id' => $lossAccountId,
                'currency_id' => $base->id,
                'exchange_rate' => 1,
                'debit' => $totalLoss,
                'credit' => 0,
                'description' => __('Foreign exchange loss :year', ['year' => $year]),
            ];
        }

        if (count($lines) < 2) {
            throw new DomainException(__('No FX differences found for annual closing.'));
        }

        return $this->journals->create([
            'entry_date' => $asOf,
            'description' => __('Annual FX closing :year', ['year' => $year]),
            'currency_id' => $base->id,
            'exchange_rate' => 1,
            'reference_type' => 'annual_fx_closing',
            'reference_id' => $year,
            'created_by' => $createdBy,
        ], $lines, $createdBy);
    }
}

<?php

namespace App\Services;

use App\Enums\ExchangeRateType;
use App\Exceptions\DomainException;
use App\Models\Currency;
use App\Models\ExchangeRate;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

class ExchangeRateService
{
    public function baseCurrency(): Currency
    {
        return Currency::query()->base()->firstOrFail();
    }

    public function rate(int $fromCurrencyId, int $toCurrencyId, CarbonInterface|string|null $date = null): float
    {
        if ($fromCurrencyId === $toCurrencyId) {
            return 1.0;
        }

        $asOf = $this->resolveDate($date);

        $direct = $this->latestPairRate($fromCurrencyId, $toCurrencyId, $asOf);
        if ($direct !== null) {
            return $direct;
        }

        $inverse = $this->latestPairRate($toCurrencyId, $fromCurrencyId, $asOf);
        if ($inverse !== null && $inverse > 0) {
            return round(1 / $inverse, 10);
        }

        throw new DomainException(__('No exchange rate found for the selected currencies and date.'));
    }

    public function rateToBase(int $fromCurrencyId, CarbonInterface|string|null $date = null): float
    {
        $base = $this->baseCurrency();

        return $this->rate($fromCurrencyId, $base->id, $date);
    }

    /**
     * Closing rate for year Y: prefer rate on Y-12-31, else latest rate recorded in that year.
     */
    public function closingRate(int $fromCurrencyId, int $toCurrencyId, int $year): float
    {
        if ($fromCurrencyId === $toCurrencyId) {
            return 1.0;
        }

        $yearEnd = Carbon::create($year, 12, 31)->startOfDay();
        $yearStart = Carbon::create($year, 1, 1)->startOfDay();

        $onYearEnd = ExchangeRate::query()
            ->active()
            ->forPair($fromCurrencyId, $toCurrencyId)
            ->whereDate('rate_date', $yearEnd->toDateString())
            ->orderByDesc('id')
            ->value('rate');

        if ($onYearEnd !== null) {
            return (float) $onYearEnd;
        }

        $inverseOnYearEnd = ExchangeRate::query()
            ->active()
            ->forPair($toCurrencyId, $fromCurrencyId)
            ->whereDate('rate_date', $yearEnd->toDateString())
            ->orderByDesc('id')
            ->value('rate');

        if ($inverseOnYearEnd !== null && (float) $inverseOnYearEnd > 0) {
            return round(1 / (float) $inverseOnYearEnd, 10);
        }

        $latestInYear = ExchangeRate::query()
            ->active()
            ->forPair($fromCurrencyId, $toCurrencyId)
            ->whereDate('rate_date', '>=', $yearStart->toDateString())
            ->whereDate('rate_date', '<=', $yearEnd->toDateString())
            ->orderByDesc('rate_date')
            ->orderByDesc('id')
            ->value('rate');

        if ($latestInYear !== null) {
            return (float) $latestInYear;
        }

        $inverseLatestInYear = ExchangeRate::query()
            ->active()
            ->forPair($toCurrencyId, $fromCurrencyId)
            ->whereDate('rate_date', '>=', $yearStart->toDateString())
            ->whereDate('rate_date', '<=', $yearEnd->toDateString())
            ->orderByDesc('rate_date')
            ->orderByDesc('id')
            ->value('rate');

        if ($inverseLatestInYear !== null && (float) $inverseLatestInYear > 0) {
            return round(1 / (float) $inverseLatestInYear, 10);
        }

        // Fall back to any rate on or before year end.
        return $this->rate($fromCurrencyId, $toCurrencyId, $yearEnd);
    }

    /**
     * @param  array{from_currency_id:int,to_currency_id:int,rate:float|string,rate_date:string,rate_type?:string|ExchangeRateType,source?:?string,is_active?:bool}  $data
     */
    public function upsertDailyRate(array $data): ExchangeRate
    {
        if ((int) $data['from_currency_id'] === (int) $data['to_currency_id']) {
            throw new DomainException(__('From and to currencies must be different.'));
        }

        $rateType = $data['rate_type'] ?? ExchangeRateType::Manual;
        if (is_string($rateType)) {
            $rateType = ExchangeRateType::from($rateType);
        }

        $rateDate = Carbon::parse($data['rate_date'])->toDateString();

        $attributes = [
            'rate' => $data['rate'],
            'rate_type' => $rateType,
            'source' => $data['source'] ?? null,
            'is_active' => $data['is_active'] ?? true,
            'rate_date' => $rateDate,
        ];

        $existing = ExchangeRate::query()
            ->forPair((int) $data['from_currency_id'], (int) $data['to_currency_id'])
            ->whereDate('rate_date', $rateDate)
            ->first();

        if ($existing) {
            $existing->fill($attributes);
            $existing->save();

            return $existing->fresh();
        }

        return ExchangeRate::query()->create([
            'from_currency_id' => $data['from_currency_id'],
            'to_currency_id' => $data['to_currency_id'],
            ...$attributes,
        ]);
    }

    protected function latestPairRate(int $fromCurrencyId, int $toCurrencyId, CarbonInterface $asOf): ?float
    {
        $rate = ExchangeRate::query()
            ->active()
            ->forPair($fromCurrencyId, $toCurrencyId)
            ->whereDate('rate_date', '<=', $asOf->toDateString())
            ->orderByDesc('rate_date')
            ->orderByDesc('id')
            ->value('rate');

        return $rate !== null ? (float) $rate : null;
    }

    protected function resolveDate(CarbonInterface|string|null $date): CarbonInterface
    {
        if ($date instanceof CarbonInterface) {
            return $date->copy()->startOfDay();
        }

        if (is_string($date) && $date !== '') {
            return Carbon::parse($date)->startOfDay();
        }

        return now()->startOfDay();
    }
}

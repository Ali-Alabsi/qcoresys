<?php

namespace Database\Seeders;

use App\Enums\ExchangeRateType;
use App\Models\Currency;
use App\Services\ExchangeRateService;
use Illuminate\Database\Seeder;

class ExchangeRateSeeder extends Seeder
{
    public function run(): void
    {
        $usd = Currency::query()->where('code', 'USD')->first();
        $sar = Currency::query()->where('code', 'SAR')->first();
        $yer = Currency::query()->where('code', 'YER')->first();

        if (! $usd || ! $sar || ! $yer) {
            return;
        }

        $service = app(ExchangeRateService::class);
        $today = now()->toDateString();

        // Market quotes: units of target per 1 unit of source
        // USD→YER 535, USD→SAR 3.80, SAR→YER 140 (+ inverses for lookup)
        $pairs = [
            [$usd->id, $yer->id, 535.0],
            [$usd->id, $sar->id, 3.80],
            [$sar->id, $yer->id, 140.0],
            [$yer->id, $usd->id, round(1 / 535, 10)],
            [$sar->id, $usd->id, round(1 / 3.80, 10)],
            [$yer->id, $sar->id, round(1 / 140, 10)],
        ];

        foreach ($pairs as [$fromId, $toId, $rate]) {
            $service->upsertDailyRate([
                'from_currency_id' => $fromId,
                'to_currency_id' => $toId,
                'rate' => $rate,
                'rate_date' => $today,
                'rate_type' => ExchangeRateType::Manual,
                'source' => 'seed',
                'is_active' => true,
            ]);
        }
    }
}

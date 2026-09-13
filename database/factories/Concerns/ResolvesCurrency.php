<?php

namespace Database\Factories\Concerns;

use App\Models\Currency;

trait ResolvesCurrency
{
    protected static function baseCurrencyId(): int
    {
        $currencyId = Currency::query()
            ->where('is_base_currency', true)
            ->value('id');

        if ($currencyId) {
            return $currencyId;
        }

        return Currency::factory()->create([
            'code' => 'USD',
            'name' => 'US Dollar',
            'symbol' => '$',
            'is_base_currency' => true,
        ])->id;
    }
}

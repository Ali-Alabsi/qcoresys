<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    public function run(): void
    {
        Currency::query()->where('is_base_currency', true)->update(['is_base_currency' => false]);

        // Remove EUR permanently from the platform (deactivate if already seeded).
        Currency::query()->where('code', 'EUR')->update(['is_active' => false, 'is_base_currency' => false]);

        $currencies = [
            [
                'code' => 'USD',
                'name' => 'US Dollar',
                'symbol' => '$',
                'decimal_places' => 2,
                'is_base_currency' => true,
                'is_active' => true,
            ],
            [
                'code' => 'SAR',
                'name' => 'Saudi Riyal',
                'symbol' => 'ر.س',
                'decimal_places' => 2,
                'is_base_currency' => false,
                'is_active' => true,
            ],
            [
                'code' => 'YER',
                'name' => 'Yemeni Rial',
                'symbol' => '﷼',
                'decimal_places' => 2,
                'is_base_currency' => false,
                'is_active' => true,
            ],
        ];

        foreach ($currencies as $currency) {
            Currency::query()->updateOrCreate(
                ['code' => $currency['code']],
                $currency
            );
        }
    }
}

<?php

namespace Database\Factories;

use App\Enums\ExchangeRateType;
use App\Models\Currency;
use App\Models\ExchangeRate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExchangeRate>
 */
class ExchangeRateFactory extends Factory
{
    protected $model = ExchangeRate::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $from = Currency::factory()->create();
        $to = Currency::query()->base()->first() ?? Currency::factory()->base()->create();

        return [
            'from_currency_id' => $from->id,
            'to_currency_id' => $to->id,
            'rate' => fake()->randomFloat(6, 0.001, 5),
            'rate_date' => now()->toDateString(),
            'rate_type' => ExchangeRateType::Manual,
            'source' => 'manual',
            'is_active' => true,
        ];
    }
}

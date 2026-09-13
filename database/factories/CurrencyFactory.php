<?php

namespace Database\Factories;

use App\Models\Currency;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Currency>
 */
class CurrencyFactory extends Factory
{
    protected $model = Currency::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $code = fake()->unique()->currencyCode();

        return [
            'code' => $code,
            'name' => fake()->words(2, true),
            'symbol' => substr($code, 0, 1),
            'decimal_places' => 2,
            'is_base_currency' => false,
            'is_active' => true,
        ];
    }

    public function base(): static
    {
        return $this->state(fn (array $attributes) => [
            'code' => 'USD',
            'name' => 'US Dollar',
            'symbol' => '$',
            'is_base_currency' => true,
        ]);
    }
}

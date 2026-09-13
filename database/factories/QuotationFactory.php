<?php

namespace Database\Factories;

use App\Enums\QuotationStatus;
use App\Models\Customer;
use App\Models\Quotation;
use App\Models\User;
use Database\Factories\Concerns\ResolvesCurrency;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Quotation>
 */
class QuotationFactory extends Factory
{
    use ResolvesCurrency;

    protected $model = Quotation::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 5000, 150000);
        $taxAmount = round($subtotal * 0.15, 2);

        return [
            'customer_id' => Customer::factory(),
            'quotation_date' => now()->toDateString(),
            'valid_until' => now()->addDays(30)->toDateString(),
            'version' => 1,
            'status' => QuotationStatus::Draft,
            'currency_id' => static::baseCurrencyId(),
            'exchange_rate' => 1,
            'subtotal' => $subtotal,
            'discount_amount' => 0,
            'tax_amount' => $taxAmount,
            'other_amount' => 0,
            'total_amount' => $subtotal + $taxAmount,
            'prepared_by' => User::factory(),
        ];
    }
}

<?php

namespace Database\Factories;

use App\Enums\InvoiceStatus;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\User;
use Database\Factories\Concerns\ResolvesCurrency;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    use ResolvesCurrency;

    protected $model = Invoice::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 1000, 100000);
        $taxAmount = round($subtotal * 0.15, 2);
        $total = $subtotal + $taxAmount;

        return [
            'customer_id' => Customer::factory(),
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'currency_id' => static::baseCurrencyId(),
            'exchange_rate' => 1,
            'subtotal' => $subtotal,
            'discount_amount' => 0,
            'tax_amount' => $taxAmount,
            'other_amount' => 0,
            'total_amount' => $total,
            'paid_amount' => 0,
            'remaining_amount' => $total,
            'status' => InvoiceStatus::Draft,
            'issued_by' => User::factory(),
        ];
    }
}

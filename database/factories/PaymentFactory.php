<?php

namespace Database\Factories;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\User;
use Database\Factories\Concerns\ResolvesAccount;
use Database\Factories\Concerns\ResolvesCurrency;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    use ResolvesAccount, ResolvesCurrency;

    protected $model = Payment::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'account_id' => static::defaultCashAccountId(),
            'payment_method' => fake()->randomElement(PaymentMethod::cases()),
            'payment_date' => now()->toDateString(),
            'amount' => fake()->randomFloat(2, 500, 50000),
            'currency_id' => static::baseCurrencyId(),
            'exchange_rate' => 1,
            'status' => PaymentStatus::Draft,
            'received_by' => User::factory(),
        ];
    }
}

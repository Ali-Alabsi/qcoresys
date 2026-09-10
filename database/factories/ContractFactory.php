<?php

namespace Database\Factories;

use App\Enums\ContractStatus;
use App\Enums\ContractType;
use App\Models\Contract;
use App\Models\Customer;
use Database\Factories\Concerns\ResolvesCurrency;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Contract>
 */
class ContractFactory extends Factory
{
    use ResolvesCurrency;

    protected $model = Contract::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('-1 month', '+1 month');

        return [
            'customer_id' => Customer::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'contract_type' => fake()->randomElement(ContractType::cases()),
            'start_date' => $startDate,
            'end_date' => (clone $startDate)->modify('+6 months'),
            'contract_amount' => fake()->randomFloat(2, 25000, 500000),
            'currency_id' => static::baseCurrencyId(),
            'payment_terms' => 'Net 30',
            'status' => ContractStatus::Draft,
            'auto_renew' => false,
        ];
    }
}

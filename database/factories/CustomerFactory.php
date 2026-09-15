<?php

namespace Database\Factories;

use App\Enums\CustomerStatus;
use App\Enums\CustomerType;
use App\Models\Account;
use App\Models\Customer;
use App\Services\CustomerAccountService;
use Database\Factories\Concerns\ResolvesCurrency;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    use ResolvesCurrency;

    protected $model = Customer::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(CustomerType::cases());
        $companyName = fake()->company();

        return [
            'customer_type' => $type,
            'name' => $type === CustomerType::Individual ? fake()->name() : $companyName,
            'company_name' => $type === CustomerType::Individual ? null : $companyName,
            'email' => fake()->unique()->companyEmail(),
            'phone' => fake()->phoneNumber(),
            'mobile' => fake()->phoneNumber(),
            'country' => 'SA',
            'city' => fake()->city(),
            'address' => fake()->address(),
            'industry' => fake()->randomElement(['Technology', 'Finance', 'Healthcare', 'Government', 'Retail']),
            'status' => CustomerStatus::Active,
            'default_currency_id' => static::baseCurrencyId(),
            'credit_limit' => fake()->randomFloat(2, 10000, 500000),
            'payment_terms_days' => fake()->randomElement([15, 30, 45, 60]),
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Customer $customer) {
            if ($customer->account_id) {
                return;
            }

            if (! Account::query()->where('account_code', '1121')->exists()) {
                return;
            }

            app(CustomerAccountService::class)->ensureFor($customer);
        });
    }
}

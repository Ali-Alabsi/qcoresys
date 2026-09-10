<?php

namespace Database\Factories;

use App\Enums\AccountType;
use App\Enums\NormalBalance;
use App\Models\Account;
use Database\Factories\Concerns\ResolvesCurrency;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Account>
 */
class AccountFactory extends Factory
{
    use ResolvesCurrency;

    protected $model = Account::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(AccountType::cases());
        $normalBalance = in_array($type, [AccountType::Asset, AccountType::Expense], true)
            ? NormalBalance::Debit
            : NormalBalance::Credit;

        return [
            'account_code' => (string) fake()->unique()->numberBetween(6000, 9999),
            'account_name' => fake()->words(3, true),
            'account_type' => $type,
            'account_level' => 1,
            'normal_balance' => $normalBalance,
            'currency_id' => static::baseCurrencyId(),
            'is_control_account' => false,
            'is_cash_account' => false,
            'is_bank_account' => false,
            'is_customer_account' => false,
            'is_vendor_account' => false,
            'allow_posting' => true,
            'opening_balance' => 0,
            'opening_debit' => 0,
            'opening_credit' => 0,
            'current_balance' => 0,
            'is_active' => true,
        ];
    }

    public function cash(): static
    {
        return $this->state(fn (array $attributes) => [
            'account_type' => AccountType::Asset,
            'normal_balance' => NormalBalance::Debit,
            'is_cash_account' => true,
            'account_name' => 'Cash',
        ]);
    }

    public function expense(): static
    {
        return $this->state(fn (array $attributes) => [
            'account_type' => AccountType::Expense,
            'normal_balance' => NormalBalance::Debit,
            'account_name' => 'Operating Expense',
        ]);
    }
}

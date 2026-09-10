<?php

namespace Database\Factories;

use App\Enums\ExpenseStatus;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Database\Factories\Concerns\ResolvesAccount;
use Database\Factories\Concerns\ResolvesCurrency;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Expense>
 */
class ExpenseFactory extends Factory
{
    use ResolvesAccount, ResolvesCurrency;

    protected $model = Expense::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $amount = fake()->randomFloat(2, 100, 10000);
        $taxAmount = round($amount * 0.15, 2);

        return [
            'category_id' => ExpenseCategory::query()->value('id')
                ?? ExpenseCategory::factory()->create()->id,
            'account_id' => static::defaultExpenseAccountId(),
            'expense_date' => now()->toDateString(),
            'description' => fake()->sentence(),
            'amount' => $amount,
            'tax_amount' => $taxAmount,
            'total_amount' => $amount + $taxAmount,
            'currency_id' => static::baseCurrencyId(),
            'exchange_rate' => 1,
            'status' => ExpenseStatus::Draft,
            'is_billable' => false,
        ];
    }
}

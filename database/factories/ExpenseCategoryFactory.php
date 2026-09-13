<?php

namespace Database\Factories;

use App\Models\ExpenseCategory;
use Database\Factories\Concerns\ResolvesAccount;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ExpenseCategory>
 */
class ExpenseCategoryFactory extends Factory
{
    use ResolvesAccount;

    protected $model = ExpenseCategory::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->words(2, true);

        return [
            'code' => Str::upper(Str::slug($name, '_')),
            'name' => ucwords($name),
            'description' => fake()->sentence(),
            'account_id' => static::defaultExpenseAccountId(),
            'is_project_expense' => false,
            'is_active' => true,
        ];
    }
}

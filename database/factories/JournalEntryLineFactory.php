<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use Database\Factories\Concerns\ResolvesCurrency;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JournalEntryLine>
 */
class JournalEntryLineFactory extends Factory
{
    use ResolvesCurrency;

    protected $model = JournalEntryLine::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $amount = fake()->randomFloat(2, 100, 10000);

        return [
            'journal_entry_id' => JournalEntry::factory(),
            'line_no' => 1,
            'account_id' => Account::factory(),
            'description' => fake()->sentence(),
            'currency_id' => static::baseCurrencyId(),
            'exchange_rate' => 1,
            'debit' => $amount,
            'credit' => 0,
            'debit_base' => $amount,
            'credit_base' => 0,
        ];
    }

    public function credit(): static
    {
        return $this->state(function (array $attributes) {
            $amount = fake()->randomFloat(2, 100, 10000);

            return [
                'debit' => 0,
                'credit' => $amount,
                'debit_base' => 0,
                'credit_base' => $amount,
            ];
        });
    }
}

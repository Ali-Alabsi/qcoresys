<?php

namespace Database\Factories;

use App\Enums\JournalStatus;
use App\Models\JournalEntry;
use Database\Factories\Concerns\ResolvesCurrency;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JournalEntry>
 */
class JournalEntryFactory extends Factory
{
    use ResolvesCurrency;

    protected $model = JournalEntry::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'entry_date' => now()->toDateString(),
            'description' => fake()->sentence(),
            'currency_id' => static::baseCurrencyId(),
            'exchange_rate' => 1,
            'total_debit' => 0,
            'total_credit' => 0,
            'status' => JournalStatus::Draft,
            'is_balanced' => false,
            'is_reversed' => false,
        ];
    }
}

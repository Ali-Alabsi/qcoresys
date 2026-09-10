<?php

namespace Database\Factories;

use App\Enums\OpportunityStage;
use App\Enums\OpportunityStatus;
use App\Models\Customer;
use App\Models\Opportunity;
use Database\Factories\Concerns\ResolvesCurrency;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Opportunity>
 */
class OpportunityFactory extends Factory
{
    use ResolvesCurrency;

    protected $model = Opportunity::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'title' => fake()->sentence(4),
            'stage' => fake()->randomElement(OpportunityStage::cases()),
            'estimated_value' => fake()->randomFloat(2, 10000, 500000),
            'currency_id' => static::baseCurrencyId(),
            'expected_close_date' => now()->addMonths(3)->toDateString(),
            'status' => OpportunityStatus::Open,
        ];
    }
}

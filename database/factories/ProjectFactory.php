<?php

namespace Database\Factories;

use App\Enums\BillingType;
use App\Enums\ProjectPriority;
use App\Enums\ProjectStatus;
use App\Enums\ProjectType;
use App\Models\Customer;
use App\Models\Project;
use Database\Factories\Concerns\ResolvesCurrency;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    use ResolvesCurrency;

    protected $model = Project::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $budget = fake()->randomFloat(2, 50000, 750000);

        return [
            'customer_id' => Customer::factory(),
            'name' => fake()->words(4, true),
            'description' => fake()->paragraph(),
            'project_type' => fake()->randomElement(ProjectType::cases()),
            'status' => ProjectStatus::Planning,
            'priority' => fake()->randomElement(ProjectPriority::cases()),
            'start_date' => now()->toDateString(),
            'expected_end_date' => now()->addMonths(6)->toDateString(),
            'budget_amount' => $budget,
            'contract_amount' => $budget,
            'currency_id' => static::baseCurrencyId(),
            'estimated_cost' => $budget * 0.6,
            'estimated_revenue' => $budget,
            'estimated_profit' => $budget * 0.4,
            'completion_percentage' => 0,
            'billing_type' => fake()->randomElement(BillingType::cases()),
        ];
    }
}

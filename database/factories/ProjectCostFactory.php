<?php

namespace Database\Factories;

use App\Enums\CostType;
use App\Models\Project;
use App\Models\ProjectCost;
use Database\Factories\Concerns\ResolvesCurrency;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProjectCost>
 */
class ProjectCostFactory extends Factory
{
    use ResolvesCurrency;

    protected $model = ProjectCost::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'cost_type' => fake()->randomElement(CostType::cases()),
            'description' => fake()->sentence(),
            'amount' => fake()->randomFloat(2, 100, 10000),
            'currency_id' => static::baseCurrencyId(),
            'exchange_rate' => 1,
            'cost_date' => now()->toDateString(),
            'billable' => fake()->boolean(60),
        ];
    }
}

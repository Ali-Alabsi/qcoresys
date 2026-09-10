<?php

namespace Database\Factories;

use App\Enums\ProjectServiceStatus;
use App\Models\Project;
use App\Models\ProjectService;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProjectService>
 */
class ProjectServiceFactory extends Factory
{
    protected $model = ProjectService::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = fake()->randomFloat(2, 1, 100);
        $unitPrice = fake()->randomFloat(2, 100, 1000);

        return [
            'project_id' => Project::factory(),
            'service_id' => Service::factory(),
            'description' => fake()->sentence(),
            'quantity' => $quantity,
            'unit' => 'hour',
            'unit_price' => $unitPrice,
            'total_amount' => round($quantity * $unitPrice, 2),
            'estimated_hours' => $quantity,
            'status' => ProjectServiceStatus::Pending,
        ];
    }
}

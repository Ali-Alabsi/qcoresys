<?php

namespace Database\Factories;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\ProjectTask;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProjectTask>
 */
class ProjectTaskFactory extends Factory
{
    protected $model = ProjectTask::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'priority' => fake()->randomElement(TaskPriority::cases()),
            'status' => TaskStatus::Todo,
            'start_date' => now()->toDateString(),
            'due_date' => now()->addWeeks(2)->toDateString(),
            'estimated_hours' => fake()->randomFloat(2, 2, 40),
            'completion_percentage' => 0,
        ];
    }
}

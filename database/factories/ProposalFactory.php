<?php

namespace Database\Factories;

use App\Enums\ProposalStatus;
use App\Models\Customer;
use App\Models\Proposal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Proposal>
 */
class ProposalFactory extends Factory
{
    protected $model = Proposal::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'title' => fake()->sentence(5),
            'executive_summary' => fake()->paragraph(),
            'problem_statement' => fake()->paragraph(),
            'proposed_solution' => fake()->paragraph(),
            'scope' => fake()->paragraph(),
            'status' => ProposalStatus::Draft,
            'prepared_by' => User::factory(),
        ];
    }
}

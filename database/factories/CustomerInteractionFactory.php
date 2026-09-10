<?php

namespace Database\Factories;

use App\Enums\InteractionStatus;
use App\Enums\InteractionType;
use App\Models\Customer;
use App\Models\CustomerInteraction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CustomerInteraction>
 */
class CustomerInteractionFactory extends Factory
{
    protected $model = CustomerInteraction::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'user_id' => User::factory(),
            'interaction_type' => fake()->randomElement(InteractionType::cases()),
            'subject' => fake()->sentence(5),
            'description' => fake()->paragraph(),
            'interaction_date' => now(),
            'status' => InteractionStatus::Completed,
            'outcome' => fake()->sentence(),
        ];
    }
}

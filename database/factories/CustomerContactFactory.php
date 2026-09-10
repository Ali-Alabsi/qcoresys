<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\CustomerContact;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CustomerContact>
 */
class CustomerContactFactory extends Factory
{
    protected $model = CustomerContact::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $firstName = fake()->firstName();
        $lastName = fake()->lastName();

        return [
            'customer_id' => Customer::factory(),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'full_name' => "{$firstName} {$lastName}",
            'job_title' => fake()->jobTitle(),
            'department' => fake()->randomElement(['IT', 'Procurement', 'Finance', 'Operations']),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'mobile' => fake()->phoneNumber(),
            'is_primary' => false,
            'is_decision_maker' => fake()->boolean(30),
            'is_billing_contact' => fake()->boolean(20),
            'is_technical_contact' => fake()->boolean(40),
            'is_active' => true,
        ];
    }

    public function primary(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_primary' => true,
        ]);
    }
}

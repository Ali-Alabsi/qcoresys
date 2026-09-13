<?php

namespace Database\Factories;

use App\Enums\ConsultationStatus;
use App\Enums\ConsultationType;
use App\Models\Consultation;
use App\Models\Customer;
use App\Models\User;
use Database\Factories\Concerns\ResolvesCurrency;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Consultation>
 */
class ConsultationFactory extends Factory
{
    use ResolvesCurrency;

    protected $model = Consultation::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'consultant_id' => User::factory(),
            'consultation_type' => fake()->randomElement(ConsultationType::cases()),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'objectives' => fake()->paragraph(),
            'consultation_date' => fake()->dateTimeBetween('-1 month', '+1 month'),
            'status' => ConsultationStatus::Scheduled,
            'billable' => fake()->boolean(70),
            'amount' => fake()->randomFloat(2, 1000, 25000),
            'currency_id' => static::baseCurrencyId(),
        ];
    }
}

<?php

namespace Database\Factories;

use App\Enums\RequestPriority;
use App\Enums\RequestStatus;
use App\Enums\RequestType;
use App\Models\Customer;
use App\Models\CustomerRequest;
use Database\Factories\Concerns\ResolvesCurrency;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CustomerRequest>
 */
class CustomerRequestFactory extends Factory
{
    use ResolvesCurrency;

    protected $model = CustomerRequest::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'request_type' => fake()->randomElement(RequestType::cases()),
            'subject' => fake()->sentence(6),
            'description' => fake()->paragraph(),
            'requirements' => fake()->paragraphs(2, true),
            'priority' => fake()->randomElement(RequestPriority::cases()),
            'source' => fake()->randomElement(['Website', 'Referral', 'Email', 'Phone', 'Partner']),
            'received_at' => now(),
            'estimated_budget' => fake()->randomFloat(2, 5000, 250000),
            'currency_id' => static::baseCurrencyId(),
            'status' => RequestStatus::New,
            'consultation_required' => fake()->boolean(60),
            'quotation_required' => true,
        ];
    }
}

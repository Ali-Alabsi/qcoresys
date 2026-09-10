<?php

namespace Database\Factories;

use App\Enums\VendorStatus;
use App\Enums\VendorType;
use App\Models\Vendor;
use Database\Factories\Concerns\ResolvesCurrency;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vendor>
 */
class VendorFactory extends Factory
{
    use ResolvesCurrency;

    protected $model = Vendor::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'vendor_type' => fake()->randomElement(VendorType::cases()),
            'name' => fake()->company(),
            'company_name' => fake()->company(),
            'email' => fake()->companyEmail(),
            'phone' => fake()->phoneNumber(),
            'country' => 'SA',
            'city' => fake()->city(),
            'address' => fake()->address(),
            'default_currency_id' => static::baseCurrencyId(),
            'payment_terms_days' => 30,
            'status' => VendorStatus::Active,
        ];
    }
}

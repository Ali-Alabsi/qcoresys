<?php

namespace Database\Factories;

use App\Enums\BillingType;
use App\Enums\ServiceType;
use App\Models\Service;
use App\Models\ServiceCategory;
use Database\Factories\Concerns\ResolvesCurrency;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    use ResolvesCurrency;

    protected $model = Service::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->words(3, true);
        $price = fake()->randomFloat(2, 500, 50000);

        return [
            'service_code' => 'SVC-'.strtoupper(Str::random(8)),
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(4)),
            'category_id' => ServiceCategory::factory(),
            'name' => ucwords($name),
            'name_ar' => ucwords($name),
            'short_name' => ucwords($name),
            'short_description' => fake()->sentence(),
            'short_description_ar' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'description_ar' => fake()->paragraph(),
            'overview' => fake()->paragraph(),
            'overview_ar' => fake()->paragraph(),
            'service_type' => fake()->randomElement(ServiceType::cases()),
            'billing_type' => fake()->randomElement(BillingType::cases()),
            'pricing_type' => \App\Enums\PricingType::QuoteRequired,
            'unit' => 'hour',
            'default_quantity' => 1,
            'default_price' => $price,
            'starting_price' => null,
            'cost_price' => $price * 0.6,
            'currency_id' => static::baseCurrencyId(),
            'tax_rate' => 15,
            'estimated_hours' => fake()->randomFloat(2, 8, 200),
            'is_consulting' => fake()->boolean(50),
            'is_project_service' => fake()->boolean(50),
            'is_recurring' => false,
            'is_active' => true,
            'is_public' => true,
            'is_featured' => false,
            'sort_order' => 0,
        ];
    }

    public function public(): static
    {
        return $this->state(fn () => [
            'is_public' => true,
            'is_active' => true,
        ]);
    }

    public function privateCatalog(): static
    {
        return $this->state(fn () => [
            'is_public' => false,
            'is_active' => true,
        ]);
    }
}

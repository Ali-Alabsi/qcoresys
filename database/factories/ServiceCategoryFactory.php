<?php

namespace Database\Factories;

use App\Models\ServiceCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ServiceCategory>
 */
class ServiceCategoryFactory extends Factory
{
    protected $model = ServiceCategory::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->words(2, true);

        return [
            'code' => 'CAT-'.strtoupper(Str::random(6)),
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(4)),
            'name' => ucwords($name),
            'name_ar' => ucwords($name),
            'description' => fake()->sentence(),
            'description_ar' => fake()->sentence(),
            'sort_order' => fake()->numberBetween(1, 100),
            'is_active' => true,
            'is_public' => true,
        ];
    }
}

<?php

namespace Database\Factories;

use App\Enums\EmploymentStatus;
use App\Models\Employee;
use Database\Factories\Concerns\ResolvesCurrency;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Employee>
 */
class EmployeeFactory extends Factory
{
    use ResolvesCurrency;

    protected $model = Employee::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'employee_no' => 'EMP-'.fake()->unique()->numerify('######'),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'job_title' => fake()->jobTitle(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'hire_date' => fake()->dateTimeBetween('-5 years', 'now'),
            'employment_status' => EmploymentStatus::Active,
            'salary' => fake()->randomFloat(2, 8000, 35000),
            'currency_id' => static::baseCurrencyId(),
        ];
    }
}

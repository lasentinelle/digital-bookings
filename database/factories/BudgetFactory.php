<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Budget>
 */
class BudgetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'year' => fake()->numberBetween(2024, 2027),
            'month' => fake()->numberBetween(1, 12),
            'amount' => fake()->numberBetween(1_000_000, 2_000_000),
        ];
    }
}

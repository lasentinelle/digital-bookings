<?php

namespace Database\Factories;

use App\Models\Budget;
use App\Models\Salesperson;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SalespersonTarget>
 */
class SalespersonTargetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'budget_id' => Budget::factory(),
            'salesperson_id' => Salesperson::factory(),
            'amount' => fake()->numberBetween(100_000, 500_000),
        ];
    }
}

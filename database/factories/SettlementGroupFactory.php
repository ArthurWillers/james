<?php

namespace Database\Factories;

use App\Models\SettlementGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SettlementGroup>
 */
class SettlementGroupFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'description' => fake()->sentence(3),
            'total_amount' => fake()->randomFloat(2, 10, 500),
            'date' => fake()->date(),
            'mode' => fake()->randomElement(['equal', 'exact']),
        ];
    }
}

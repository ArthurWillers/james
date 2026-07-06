<?php

namespace Database\Factories;

use App\Models\FinancialTag;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FinancialTag>
 */
class FinancialTagFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'color_hex' => $this->faker->hexColor(),
            'icon' => 'heroicon-o-tag',
        ];
    }
}

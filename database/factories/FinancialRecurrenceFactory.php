<?php

namespace Database\Factories;

use App\Models\FinancialAccount;
use App\Models\FinancialRecurrence;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FinancialRecurrence>
 */
class FinancialRecurrenceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->words(3, true),
            'type' => $this->faker->randomElement(['income', 'expense']),
            'amount' => $this->faker->randomFloat(2, 10, 1000),
            'frequency' => 'monthly',
            'financial_account_id' => FinancialAccount::factory(),
            'start_date' => $this->faker->date(),
            'next_processing_date' => $this->faker->date(),
        ];
    }
}

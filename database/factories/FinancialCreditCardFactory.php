<?php

namespace Database\Factories;

use App\Models\FinancialAccount;
use App\Models\FinancialCreditCard;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FinancialCreditCard>
 */
class FinancialCreditCardFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true),
            'financial_account_id' => FinancialAccount::factory(),
            'closing_day' => 10,
            'due_day' => 15,
            'credit_limit' => $this->faker->randomFloat(2, 1000, 10000),
        ];
    }
}

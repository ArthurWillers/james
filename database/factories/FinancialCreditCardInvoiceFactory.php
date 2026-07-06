<?php

namespace Database\Factories;

use App\Models\FinancialCreditCardInvoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FinancialCreditCardInvoice>
 */
class FinancialCreditCardInvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reference_month' => $this->faker->date(),
            'due_date' => $this->faker->date(),
            'closing_date' => $this->faker->date(),
        ];
    }
}

<?php

namespace Database\Factories;

use App\Enums\TransactionStatus;
use App\Models\FinancialTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FinancialTransaction>
 */
class FinancialTransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => $this->faker->randomElement(['income', 'expense']),
            'amount' => $this->faker->randomFloat(2, 10, 1000),
            'date' => $this->faker->date(),
            'description' => $this->faker->sentence(),
            'status' => $this->faker->randomElement(TransactionStatus::cases()),
        ];
    }

    public function posted(): static
    {
        return $this->state(['status' => TransactionStatus::Posted]);
    }

    public function pending(): static
    {
        return $this->state(['status' => TransactionStatus::Pending]);
    }

    public function draft(): static
    {
        return $this->state(['status' => TransactionStatus::Draft]);
    }

    public function nfce(?string $accessKey = null): static
    {
        return $this->state(fn () => [
            'type' => 'expense',
            'status' => TransactionStatus::Draft,
            'nfce_access_key' => $accessKey ?? $this->faker->unique()->numerify(str_repeat('#', 44)),
            'nfce_issuer_document' => '12345678000195',
            'nfce_source_url' => 'https://dfe-portal.svrs.rs.gov.br/Dfe/QrCodeNFce?p=43111111111111111111111111111111111111111111%7C3%7C1',
        ]);
    }
}

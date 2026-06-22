<?php

namespace Database\Factories;

use App\Enums\FinancialAccountType;
use App\Models\FinancialAccount;
use App\Models\FinancialInstitution;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends Factory<FinancialAccount>
 */
class FinancialAccountFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Model>
     */
    protected $model = FinancialAccount::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company().' Account',
            'type' => fake()->randomElement(FinancialAccountType::cases()),
            'pix_keys' => [
                [
                    'label' => 'Email',
                    'value' => fake()->unique()->safeEmail(),
                ],
                [
                    'label' => 'Telefone',
                    'value' => fake()->phoneNumber(),
                ],
            ],
        ];
    }
}

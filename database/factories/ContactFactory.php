<?php

namespace Database\Factories;

use App\Models\Contact;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Contact>
 */
class ContactFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'relationship_category' => fake()->randomElement(['Família', 'Amigos', 'Trabalho', 'Fornecedor', 'Cliente', null]),
            'birthdate' => fake()->boolean(70) ? fake()->dateTimeBetween('-60 years', '-18 years')->format('Y-m-d') : null,
            'phones' => fake()->boolean(80) ? [
                ['label' => 'Celular', 'value' => fake()->phoneNumber()],
                ['label' => 'Casa', 'value' => fake()->phoneNumber()],
            ] : null,
            'emails' => fake()->boolean(60) ? [
                ['label' => 'Pessoal', 'value' => fake()->safeEmail()],
                ['label' => 'Trabalho', 'value' => fake()->companyEmail()],
            ] : null,
            'notes' => fake()->boolean(30) ? '### '.fake()->sentence()."\n\n".fake()->realText()."\n\n- ".fake()->word()."\n- ".fake()->word() : null,
        ];
    }
}
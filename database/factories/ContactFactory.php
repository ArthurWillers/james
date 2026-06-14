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
            'phones' => fake()->boolean(80) ? [fake()->phoneNumber()] : null,
            'pix_keys' => fake()->boolean(50) ? [fake()->cpf(), fake()->email()] : null,
            'addresses' => fake()->boolean(40) ? [fake()->address()] : null,
            'notes' => fake()->boolean(30) ? '### '.fake()->sentence()."\n\n".fake()->realText()."\n\n- ".fake()->word()."\n- ".fake()->word() : null,
        ];
    }
}

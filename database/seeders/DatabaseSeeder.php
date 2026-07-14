<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(FinancialTagSeeder::class);

        User::firstOrCreate(
            ['email' => 'admin@james.test'],
            [
                'name' => 'Admin',
                'password' => bcrypt('password'), // Senha padrão para acesso inicial
            ]
        );

    }
}

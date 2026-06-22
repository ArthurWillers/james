<?php

namespace Database\Seeders;

use App\Enums\FinancialAccountType;
use App\Models\FinancialAccount;
use Illuminate\Database\Seeder;

class FinancialAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Conta Corrente Principal
        FinancialAccount::factory()->create([
            'name' => 'Conta Corrente',
            'type' => FinancialAccountType::Checking,
        ]);

        // 2. Reserva de Emergência / Liquidez Diária
        FinancialAccount::factory()->create([
            'name' => 'CDB (Reserva)',
            'type' => FinancialAccountType::Checking,
        ]);

        // 3. Título Público (Investimento Seguro)
        FinancialAccount::factory()->create([
            'name' => 'Título Público',
            'type' => FinancialAccountType::Investment,
        ]);

        // 4. CDB Específico (Apostas/Lazer)
        FinancialAccount::factory()->create([
            'name' => 'CDB Específico (Apostas)',
            'type' => FinancialAccountType::Investment,
        ]);

        // 5. Dinheiro Físico / Carteira
        FinancialAccount::factory()->create([
            'name' => 'Carteira',
            'type' => FinancialAccountType::Wallet,
        ]);

        // Criar mais algumas contas aleatórias variadas para volume de teste
        FinancialAccount::factory()->count(10)->create();
    }
}

<?php

namespace Database\Seeders;

use App\Models\FinancialTag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FinancialTagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tags = [
            [
                'id' => FinancialTag::REEMBOLSO_ID,
                'name' => 'Reembolso',
                'icon' => 'arrow-uturn-left',
                'color_hex' => '#f59e0b', // amber-500
                'is_protected' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => FinancialTag::JUROS_ID,
                'name' => 'Juros',
                'icon' => 'percent',
                'color_hex' => '#ef4444', // red-500
                'is_protected' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => FinancialTag::SALDO_INICIAL_ID,
                'name' => 'Saldo Inicial',
                'icon' => 'flag',
                'color_hex' => '#3b82f6', // blue-500
                'is_protected' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('financial_tags')->upsert(
            $tags,
            ['id'],
            ['name', 'icon', 'color_hex', 'is_protected', 'updated_at']
        );
    }
}

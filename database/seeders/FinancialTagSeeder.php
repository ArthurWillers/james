<?php

namespace Database\Seeders;

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
                'name' => 'Reembolso',
                'icon' => 'heroicon-o-arrow-uturn-left',
                'color_hex' => '#64748b', // slate-500
                'is_protected' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Juros',
                'icon' => 'heroicon-o-receipt-percent',
                'color_hex' => '#ef4444', // red-500
                'is_protected' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Saldo Inicial',
                'icon' => 'heroicon-o-flag',
                'color_hex' => '#3b82f6', // blue-500
                'is_protected' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Transferência',
                'icon' => 'heroicon-o-arrows-right-left',
                'color_hex' => '#8b5cf6', // violet-500
                'is_protected' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Ajuste de Saldo',
                'icon' => 'heroicon-o-adjustments-horizontal',
                'color_hex' => '#f59e0b', // amber-500
                'is_protected' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Adiciona as tags padrão do sistema como tags desprotegidas (podem ser editadas/apagadas)
        $defaultTags = config('finance.default_tags', []);
        foreach ($defaultTags as $tag) {
            $tags[] = [
                'name' => $tag['name'],
                'icon' => $tag['icon'],
                'color_hex' => $tag['color_hex'],
                'is_protected' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('financial_tags')->upsert(
            $tags,
            ['name'],
            ['icon', 'color_hex', 'is_protected', 'updated_at']
        );
    }
}

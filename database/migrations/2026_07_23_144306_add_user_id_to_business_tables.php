<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * As tabelas de domínio de negócio que receberão o isolamento de tenant.
     */
    protected array $tables = [
        'contacts',
        'contact_groups',
        'financial_accounts',
        'financial_credit_cards',
        'financial_credit_card_invoices',
        'financial_recurrences',
        'financial_tags',
        'financial_transactions',
        'financial_transaction_items',
        'settlements',
        'settlement_groups',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Adicionar a coluna user_id como nullable em todas as tabelas
        foreach ($this->tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
            });
        }

        // 2. Tratar dados existentes vinculando ao primeiro usuário (se existir)
        $defaultUser = DB::table('users')->first();

        if ($defaultUser) {
            foreach ($this->tables as $tableName) {
                DB::table($tableName)->update(['user_id' => $defaultUser->id]);
            }
        }

        // 3. Alterar a coluna para not null e adicionar a Foreign Key com Cascade
        foreach ($this->tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                // No Laravel nativo, usamos o change() para remover o nullable
                $table->unsignedBigInteger('user_id')->nullable(false)->change();
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            });
        }
    }
};

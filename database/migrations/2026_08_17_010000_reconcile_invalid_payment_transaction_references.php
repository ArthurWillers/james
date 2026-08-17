<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('financial_credit_card_invoices')
            ->whereNotNull('payment_transaction_id')
            ->whereNotExists(function ($query): void {
                $query->selectRaw('1')
                    ->from('financial_transactions')
                    ->whereColumn(
                        'financial_transactions.id',
                        'financial_credit_card_invoices.payment_transaction_id'
                    );
            })
            ->update(['payment_transaction_id' => null]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Invalid references cannot be restored after reconciliation.
    }
};

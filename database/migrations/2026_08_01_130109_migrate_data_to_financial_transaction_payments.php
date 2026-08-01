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
        DB::table('financial_transactions')
            ->orderBy('id')
            ->chunkById(500, function ($transactions) {
                $payments = [];
                $now = now();

                foreach ($transactions as $transaction) {
                    $payments[] = [
                        'financial_transaction_id' => $transaction->id,
                        'financial_account_id' => $transaction->financial_account_id,
                        'financial_credit_card_invoice_id' => $transaction->financial_credit_card_invoice_id,
                        'amount' => $transaction->amount,
                        'is_posted' => $transaction->is_posted,
                        'created_at' => $transaction->created_at ?? $now,
                        'updated_at' => $transaction->updated_at ?? $now,
                    ];
                }

                DB::table('financial_transaction_payments')->insert($payments);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('financial_transaction_payments')->delete();
    }
};

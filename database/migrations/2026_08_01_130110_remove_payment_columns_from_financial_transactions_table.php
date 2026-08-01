<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('financial_transactions', function (Blueprint $table) {
            $table->dropForeign(['financial_account_id']);
            $table->dropForeign(['financial_credit_card_invoice_id']);

            $table->dropIndex(['financial_account_id', 'is_posted']);
            $table->dropIndex(['financial_credit_card_invoice_id']);

            $table->dropColumn([
                'financial_account_id',
                'financial_credit_card_invoice_id',
                'is_posted',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('financial_transactions', function (Blueprint $table) {
            $table->foreignId('financial_account_id')->nullable()->constrained('financial_accounts')->restrictOnDelete();
            $table->foreignId('financial_credit_card_invoice_id')->nullable()->constrained('financial_credit_card_invoices')->nullOnDelete();
            $table->boolean('is_posted')->default(true);

            $table->index(['financial_account_id', 'is_posted']);
            $table->index('financial_credit_card_invoice_id');
        });

        // Restaura os dados usando o pagamento com maior valor daquela transação
        DB::table('financial_transactions')
            ->orderBy('id')
            ->chunkById(500, function ($transactions) {
                foreach ($transactions as $transaction) {
                    $payment = DB::table('financial_transaction_payments')
                        ->where('financial_transaction_id', $transaction->id)
                        ->orderBy('amount', 'desc')
                        ->first();

                    if ($payment) {
                        DB::table('financial_transactions')
                            ->where('id', $transaction->id)
                            ->update([
                                'financial_account_id' => $payment->financial_account_id,
                                'financial_credit_card_invoice_id' => $payment->financial_credit_card_invoice_id,
                                'is_posted' => $payment->is_posted,
                            ]);
                    }
                }
            });
    }
};

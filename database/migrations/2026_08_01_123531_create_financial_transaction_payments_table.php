<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('financial_transaction_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('financial_transaction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('financial_account_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('financial_credit_card_invoice_id')->nullable()->constrained()->restrictOnDelete();
            $table->decimal('amount', 10, 2);
            $table->boolean('is_posted')->default(true);
            $table->timestamps();

            $table->index(['financial_account_id', 'is_posted']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_transaction_payments');
    }
};

<?php

use App\Models\FinancialCreditCard;
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
        Schema::create('financial_credit_card_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(FinancialCreditCard::class)->constrained()->restrictOnDelete();
            $table->date('reference_month');
            $table->date('closing_date');
            $table->date('due_date');
            $table->date('paid_at')->nullable();
            $table->unsignedBigInteger('interest_transaction_id')->nullable();
            $table->timestamps();

            $table->unique(['financial_credit_card_id', 'reference_month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_credit_card_invoices');
    }
};

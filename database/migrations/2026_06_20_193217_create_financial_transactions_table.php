<?php

use App\Models\FinancialAccount;
use App\Models\FinancialCreditCardInvoice;
use App\Models\FinancialRecurrence;
use App\Models\FinancialTransaction;
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
        Schema::create('financial_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(FinancialAccount::class)->nullable()->constrained()->nullOnDelete();
            $table->foreignIdFor(FinancialCreditCardInvoice::class)->nullable()->constrained()->nullOnDelete();
            $table->string('type');
            $table->decimal('amount', 10, 2);
            $table->string('description');
            $table->date('date');
            $table->boolean('is_posted')->default(true);
            $table->foreignIdFor(FinancialTransaction::class, 'transfer_pair_id')->nullable()->constrained('financial_transactions')->nullOnDelete();
            $table->unsignedTinyInteger('installment_current')->nullable();
            $table->unsignedTinyInteger('installment_total')->nullable();
            $table->foreignIdFor(FinancialRecurrence::class)->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['financial_account_id', 'is_posted']);
            $table->index('date');
            $table->index('financial_credit_card_invoice_id');
        });

        Schema::table('financial_credit_card_invoices', function (Blueprint $table) {
            $table->foreign('interest_transaction_id')
                ->references('id')->on('financial_transactions')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('financial_credit_card_invoices', function (Blueprint $table) {
            $table->dropForeign(['interest_transaction_id']);
        });

        Schema::dropIfExists('financial_transactions');
    }
};

<?php

namespace App\Console\Commands;

use App\Models\FinancialTransaction;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

#[Signature('finance:rollover-transactions')]
#[Description('Efetiva transações pendentes (exceto as de fatura de cartão) cuja data já chegou')]
class RolloverDueTransactions extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $updatedCount = \App\Models\FinancialTransactionPayment::query()
            ->join('financial_transactions as ft', 'ft.id', '=', 'financial_transaction_payments.financial_transaction_id')
            ->where('financial_transaction_payments.is_posted', false)
            ->whereNull('financial_transaction_payments.financial_credit_card_invoice_id')
            ->where('ft.date', '<=', Carbon::today())
            ->update(['financial_transaction_payments.is_posted' => true]);

        $this->info("Pagamentos de transações efetivados com sucesso: {$updatedCount}");
    }
}

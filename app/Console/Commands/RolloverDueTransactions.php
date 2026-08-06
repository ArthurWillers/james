<?php

namespace App\Console\Commands;

use App\Enums\TransactionStatus;
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
        $updatedCount = FinancialTransaction::where('status', TransactionStatus::Pending)
            ->whereNull('financial_credit_card_invoice_id')
            ->where('date', '<=', Carbon::today())
            ->update(['status' => TransactionStatus::Posted]);

        $this->info("Transações efetivadas com sucesso: {$updatedCount}");
    }
}

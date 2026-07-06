<?php

namespace App\Console\Commands;

use App\Models\FinancialCreditCard;
use App\Models\FinancialCreditCardInvoice;
use Illuminate\Console\Command;

class RolloverCreditCardInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'finance:rollover-invoices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rollover credit card invoices for the current day';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $cards = FinancialCreditCard::all();
        $today = now();

        foreach ($cards as $card) {
            FinancialCreditCardInvoice::resolveForDate($card, $today);
        }

        $this->info('Credit card invoices rolled over successfully.');

        return Command::SUCCESS;
    }
}

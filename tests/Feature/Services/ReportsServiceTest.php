<?php

use App\Models\FinancialAccount;
use App\Models\FinancialCreditCard;
use App\Models\FinancialCreditCardInvoice;
use App\Models\FinancialTransaction;
use App\Services\ReportsService;
use Illuminate\Support\Carbon;

it('calculates net worth evolution correctly based on accrual accounting (competência)', function () {
    $account = FinancialAccount::factory()->create();
    $card = FinancialCreditCard::factory()->create(['financial_account_id' => $account->id]);

    $invoice = FinancialCreditCardInvoice::factory()->create([
        'financial_credit_card_id' => $card->id,
        'reference_month' => Carbon::now()->startOfMonth(),
        'closing_date' => Carbon::now()->addDays(20),
        'due_date' => Carbon::now()->addDays(25),
    ]);

    // An income on account today
    FinancialTransaction::factory()->create([
        'financial_account_id' => $account->id,
        'type' => 'income',
        'amount' => 1000,
        'date' => Carbon::now()->format('Y-m-d'),
        'is_posted' => true,
    ]);

    // A credit card expense today (competência: impacts net worth TODAY, not on invoice due date)
    FinancialTransaction::factory()->create([
        'financial_credit_card_invoice_id' => $invoice->id,
        'type' => 'expense',
        'amount' => 300,
        'date' => Carbon::now()->format('Y-m-d'),
        'is_posted' => true,
    ]);

    $service = new ReportsService;

    // Test evolution for this month, daily
    $startDate = Carbon::now()->startOfMonth();
    $endDate = Carbon::now()->endOfMonth();

    $data = $service->getAll($startDate, $endDate, null, 'daily')['netWorthEvolution'];

    // We can't easily assert exactly which index is today because it depends on the day of the month,
    // but the final net worth at the end of the data array should reflect 1000 - 300 = 700.

    $lastPoint = end($data);
    expect($lastPoint['value'])->toEqual(700);
});

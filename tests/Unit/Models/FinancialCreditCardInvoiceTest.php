<?php

use App\Enums\TransactionStatus;
use App\Models\FinancialCreditCard;
use App\Models\FinancialCreditCardInvoice;
use App\Models\FinancialTransaction;

it('calculates total invoice amount correctly', function () {
    $card = FinancialCreditCard::factory()->create();
    $invoice = FinancialCreditCardInvoice::factory()->create(['financial_credit_card_id' => $card->id]);

    // Add 2 expenses and 1 income
    FinancialTransaction::factory()->create([
        'financial_credit_card_invoice_id' => $invoice->id,
        'type' => 'expense',
        'amount' => 100.50,
        'status' => TransactionStatus::Posted,
    ]);

    FinancialTransaction::factory()->create([
        'financial_credit_card_invoice_id' => $invoice->id,
        'type' => 'expense',
        'amount' => 50.25,
        'status' => TransactionStatus::Posted,
    ]);

    // Income inside credit card reduces the invoice total (like cashbacks)
    FinancialTransaction::factory()->create([
        'financial_credit_card_invoice_id' => $invoice->id,
        'type' => 'income',
        'amount' => 10.00,
        'status' => TransactionStatus::Posted,
    ]);

    expect($invoice->transactions)->toHaveCount(3)
        // Total should be 100.50 + 50.25 - 10.00 = 140.75
        ->and($invoice->total())->toEqual(140.75);
});

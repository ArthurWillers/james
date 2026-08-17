<?php

use App\Enums\TransactionStatus;
use App\Models\FinancialAccount;
use App\Models\FinancialCreditCard;
use App\Models\FinancialCreditCardInvoice;
use App\Models\FinancialTag;
use App\Models\FinancialTransaction;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

it('calculates total invoice amount correctly', function () {
    $card = FinancialCreditCard::factory()->create();
    $invoice = FinancialCreditCardInvoice::factory()->create(['financial_credit_card_id' => $card->id]);

    // Add 2 expenses and 1 income
    FinancialTransaction::factory()->create([
        'financial_credit_card_invoice_id' => $invoice->id,
        'type' => 'expense',
        'amount' => 100.50,
        'status' => 'posted',
    ]);

    FinancialTransaction::factory()->create([
        'financial_credit_card_invoice_id' => $invoice->id,
        'type' => 'expense',
        'amount' => 50.25,
        'status' => 'posted',
    ]);

    // Income inside credit card reduces the invoice total (like cashbacks)
    FinancialTransaction::factory()->create([
        'financial_credit_card_invoice_id' => $invoice->id,
        'type' => 'income',
        'amount' => 10.00,
        'status' => 'posted',
    ]);

    expect($invoice->transactions)->toHaveCount(3)
        // Total should be 100.50 + 50.25 - 10.00 = 140.75
        ->and($invoice->total())->toEqual(140.75);
});

it('excludes drafts from invoice totals and the card used limit', function () {
    $card = FinancialCreditCard::factory()->create();
    $invoice = FinancialCreditCardInvoice::factory()->create([
        'financial_credit_card_id' => $card->id,
        'paid_at' => null,
    ]);

    FinancialTransaction::factory()->create([
        'financial_credit_card_invoice_id' => $invoice->id,
        'type' => 'expense',
        'amount' => 75,
        'status' => TransactionStatus::Pending,
    ]);
    $draft = FinancialTransaction::factory()->create([
        'financial_credit_card_invoice_id' => $invoice->id,
        'type' => 'expense',
        'amount' => 900,
        'status' => TransactionStatus::Draft,
    ]);

    expect($invoice->total())->toEqual(75.0)
        ->and(FinancialCreditCardInvoice::withTotalAmount()->findOrFail($invoice->id)->total())->toEqual(75.0)
        ->and(FinancialCreditCard::withUsedLimit()->findOrFail($card->id)->usedLimit())->toEqual(75.0);

    $invoice->registerPayment(75, Carbon::parse('2026-08-17'));

    expect($draft->refresh()->status)->toBe(TransactionStatus::Draft);
});

it('registers and undoes partial payments without lazy loading violations', function () {
    $account = FinancialAccount::factory()->create();
    $card = FinancialCreditCard::factory()->create(['financial_account_id' => $account->id]);
    $invoice = FinancialCreditCardInvoice::factory()->create([
        'financial_credit_card_id' => $card->id,
        'reference_month' => '2026-08-01',
    ]);
    FinancialTag::factory()->create([
        'id' => FinancialTag::PAGAMENTO_PARCIAL_ID,
        'name' => 'Pagamento parcial',
    ]);
    FinancialTransaction::factory()->create([
        'financial_credit_card_invoice_id' => $invoice->id,
        'financial_account_id' => null,
        'type' => 'expense',
        'amount' => 100,
        'status' => TransactionStatus::Pending,
    ]);

    $invoice->registerPayment(40, Carbon::parse('2026-08-17'));
    $paymentTransactionId = $invoice->refresh()->payment_transaction_id;

    expect($paymentTransactionId)->not->toBeNull()
        ->and((float) $invoice->amount_paid)->toBe(40.0);

    $invoice->undoPayment();

    expect($invoice->refresh())
        ->payment_transaction_id->toBeNull()
        ->amount_paid->toBe('0.00');
    $this->assertSoftDeleted('financial_transactions', ['id' => $paymentTransactionId]);
});

it('locks the invoice row while registering a payment', function () {
    $account = FinancialAccount::factory()->create();
    $card = FinancialCreditCard::factory()->create(['financial_account_id' => $account->id]);
    $invoice = FinancialCreditCardInvoice::factory()->create([
        'financial_credit_card_id' => $card->id,
    ]);
    FinancialTransaction::factory()->create([
        'financial_credit_card_invoice_id' => $invoice->id,
        'amount' => 100,
        'type' => 'expense',
        'status' => TransactionStatus::Pending,
    ]);

    $queries = [];
    DB::listen(function (QueryExecuted $query) use (&$queries): void {
        $queries[] = strtolower($query->sql);
    });

    $invoice->registerPayment(40, Carbon::parse('2026-08-17'));

    expect(collect($queries)->contains(fn (string $query): bool => str_contains($query, 'for update')))->toBeTrue();
});

it('treats the closing day as part of the next invoice period', function () {
    $card = FinancialCreditCard::factory()->create(['closing_day' => 10, 'due_day' => 15]);

    expect($card->resolveReferenceMonth(Carbon::parse('2026-08-09'))->format('Y-m'))->toBe('2026-08')
        ->and($card->resolveReferenceMonth(Carbon::parse('2026-08-10'))->format('Y-m'))->toBe('2026-09');
});

describe('resolveForDate', function () {
    it('assigns a transaction to the current month when the date is before the closing day', function () {
        // Card closes on day 10; a purchase on day 5 belongs to the current month's invoice.
        $card = FinancialCreditCard::factory()->create(['closing_day' => 10, 'due_day' => 15]);

        $invoice = FinancialCreditCardInvoice::resolveForDate($card, Carbon::parse('2026-08-05'));

        expect($invoice->reference_month->format('Y-m'))->toBe('2026-08');
    });

    it('assigns a transaction to the next month when the date equals the closing day', function () {
        // Card closes on day 10; a purchase ON the closing day belongs to the next month's invoice.
        $card = FinancialCreditCard::factory()->create(['closing_day' => 10, 'due_day' => 15]);

        $invoice = FinancialCreditCardInvoice::resolveForDate($card, Carbon::parse('2026-08-10'));

        expect($invoice->reference_month->format('Y-m'))->toBe('2026-09');
    });

    it('assigns a transaction to the next month when the date is after the closing day', function () {
        // Card closes on day 10; a purchase on day 11 belongs to the next month's invoice.
        $card = FinancialCreditCard::factory()->create(['closing_day' => 10, 'due_day' => 15]);

        $invoice = FinancialCreditCardInvoice::resolveForDate($card, Carbon::parse('2026-08-11'));

        expect($invoice->reference_month->format('Y-m'))->toBe('2026-09');
    });

    it('uses the existing invoice custom closing_date instead of recalculating from card closing_day', function () {
        // Card default closing day is 7, but this invoice was manually set to close on day 6.
        // A transaction on day 7 should therefore go to the NEXT month's invoice.
        $card = FinancialCreditCard::factory()->create(['closing_day' => 7, 'due_day' => 15]);

        FinancialCreditCardInvoice::factory()->create([
            'financial_credit_card_id' => $card->id,
            'reference_month' => '2026-08-01',
            'closing_date' => '2026-08-06',
            'due_date' => '2026-09-15',
        ]);

        // Date is on the custom closing day (06), so it should go to September.
        $invoiceOnClosingDay = FinancialCreditCardInvoice::resolveForDate($card, Carbon::parse('2026-08-06'));
        expect($invoiceOnClosingDay->reference_month->format('Y-m'))->toBe('2026-09');

        // Date is after the custom closing day (07), so it should also go to September.
        $invoiceAfterClosingDay = FinancialCreditCardInvoice::resolveForDate($card, Carbon::parse('2026-08-07'));
        expect($invoiceAfterClosingDay->reference_month->format('Y-m'))->toBe('2026-09');

        // Date before the custom closing day (05) belongs to August.
        $invoiceBeforeClosingDay = FinancialCreditCardInvoice::resolveForDate($card, Carbon::parse('2026-08-05'));
        expect($invoiceBeforeClosingDay->reference_month->format('Y-m'))->toBe('2026-08');
    });
});

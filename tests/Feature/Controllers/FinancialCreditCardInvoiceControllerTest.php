<?php

use App\Models\FinancialCreditCard;
use App\Models\FinancialCreditCardInvoice;
use App\Models\FinancialTag;
use App\Models\FinancialTransaction;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('can view invoice details', function () {
    $card = FinancialCreditCard::factory()->create();
    $invoice = FinancialCreditCardInvoice::factory()->create([
        'financial_credit_card_id' => $card->id,
    ]);

    $this->get(route('financial.cards.invoices.show', [$card, $invoice]))
        ->assertSuccessful()
        ->assertViewIs('finance.cards.invoices.show');
});

it('can update invoice notes and status', function () {
    $card = FinancialCreditCard::factory()->create();
    $invoice = FinancialCreditCardInvoice::factory()->create([
        'financial_credit_card_id' => $card->id,
    ]);

    $data = [
        'closing_date' => $invoice->closing_date->format('Y-m-d'),
        'due_date' => $invoice->due_date->format('Y-m-d'),
        'notes' => 'Fatura paga parcialmente',
    ];

    $this->put(route('financial.cards.invoices.update', [$card, $invoice]), $data)
        ->assertRedirect(route('financial.cards.invoices.show', [$card, $invoice]));

    $this->assertDatabaseHas('financial_credit_card_invoices', [
        'id' => $invoice->id,
        'notes' => 'Fatura paga parcialmente',
    ]);
});

it('can pay invoice', function () {
    $card = FinancialCreditCard::factory()->create();
    $invoice = FinancialCreditCardInvoice::factory()->create([
        'financial_credit_card_id' => $card->id,
    ]);

    FinancialTag::factory()->create(['id' => FinancialTag::PAGAMENTO_PARCIAL_ID]);

    FinancialTransaction::factory()->create([
        'financial_credit_card_invoice_id' => $invoice->id,
        'type' => 'expense',
        'amount' => 1000,
        'status' => 'pending',
    ]);

    $data = [
        'amount' => 500,
        'paid_at' => now()->format('Y-m-d'),
    ];

    $this->post(route('financial.cards.invoices.pay', [$card, $invoice]), $data)
        ->assertRedirect(route('financial.cards.invoices.show', [$card, $invoice]));

    $this->assertDatabaseHas('financial_credit_card_invoices', [
        'id' => $invoice->id,
        'amount_paid' => 500,
    ]);
});

it('can unpay invoice', function () {
    $card = FinancialCreditCard::factory()->create();
    $invoice = FinancialCreditCardInvoice::factory()->create([
        'financial_credit_card_id' => $card->id,
        'amount_paid' => 500,
        'paid_at' => now()->format('Y-m-d'),
    ]);

    $this->post(route('financial.cards.invoices.unpay', [$card, $invoice]))
        ->assertRedirect(route('financial.cards.invoices.show', [$card, $invoice]));

    $this->assertDatabaseHas('financial_credit_card_invoices', [
        'id' => $invoice->id,
        'amount_paid' => 0,
        'paid_at' => null,
    ]);
});

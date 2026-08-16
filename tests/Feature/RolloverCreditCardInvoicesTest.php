<?php

use App\Enums\NotificationLevel;
use App\Enums\TransactionStatus;
use App\Models\FinancialCreditCard;
use App\Models\FinancialCreditCardInvoice;
use App\Models\FinancialTransaction;
use App\Models\User;
use App\Notifications\GeneralNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    $this->user = User::factory()->create();
    Notification::fake();
});

it('notifies users when an invoice closing date is today', function () {
    $card = FinancialCreditCard::factory()->create();

    $invoice = FinancialCreditCardInvoice::factory()->for($card, 'creditCard')->create([
        'closing_date' => Carbon::today(),
        'due_date' => Carbon::today()->addDays(15),
        'reference_month' => Carbon::today()->startOfMonth(),
    ]);

    FinancialTransaction::factory()->create([
        'financial_credit_card_invoice_id' => $invoice->id,
        'type' => 'expense',
        'amount' => 250.00,
        'date' => Carbon::today()->subDay(),
        'status' => TransactionStatus::Pending,
    ]);

    $this->artisan('finance:rollover-invoices')->assertSuccessful();

    Notification::assertSentTo($this->user, GeneralNotification::class, function ($notification) {
        return $notification->title === 'Fatura de Cartão Fechada'
            && $notification->level === NotificationLevel::Warning
            && $notification->details['Lançamentos'] === '1 lançamento'
            && str_contains($notification->details['Valor Total'], '250,00');
    });
});

it('does not notify when no invoice closes today', function () {
    $card = FinancialCreditCard::factory()->create(['closing_day' => 25]);

    FinancialCreditCardInvoice::factory()->for($card, 'creditCard')->create([
        'closing_date' => Carbon::today()->subDay(),
        'due_date' => Carbon::today()->addDays(20),
        'reference_month' => Carbon::today()->subMonth()->startOfMonth(),
    ]);

    $this->artisan('finance:rollover-invoices')->assertSuccessful();

    Notification::assertNothingSent();
});

it('does not notify for invoices with zero balance', function () {
    $card = FinancialCreditCard::factory()->create();

    // Invoice with no transactions (total = 0)
    FinancialCreditCardInvoice::factory()->for($card, 'creditCard')->create([
        'closing_date' => Carbon::today(),
        'due_date' => Carbon::today()->addDays(15),
        'reference_month' => Carbon::today()->startOfMonth(),
    ]);

    $this->artisan('finance:rollover-invoices')->assertSuccessful();

    Notification::assertNothingSent();
});

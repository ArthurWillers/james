<?php

use App\Enums\NotificationLevel;
use App\Enums\TransactionStatus;
use App\Models\FinancialAccount;
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

it('notifies when there are pending expense transactions due today', function () {
    $account = FinancialAccount::factory()->create();

    FinancialTransaction::factory()->create([
        'financial_account_id' => $account->id,
        'type' => 'expense',
        'amount' => 200.00,
        'date' => Carbon::today(),
        'status' => TransactionStatus::Pending,
    ]);

    $this->artisan('finance:due-today-alerts')->assertSuccessful();

    Notification::assertSentTo($this->user, GeneralNotification::class, function ($notification) {
        return $notification->title === 'Vencimentos Próximos'
            && $notification->level === NotificationLevel::Warning
            && str_contains($notification->details['Valor Total'], '200,00');
    });
});

it('notifies when there are pending expense transactions due tomorrow', function () {
    $account = FinancialAccount::factory()->create();

    FinancialTransaction::factory()->create([
        'financial_account_id' => $account->id,
        'type' => 'expense',
        'amount' => 150.00,
        'date' => Carbon::tomorrow(),
        'status' => TransactionStatus::Pending,
    ]);

    $this->artisan('finance:due-today-alerts')->assertSuccessful();

    Notification::assertSentTo($this->user, GeneralNotification::class);
});

it('notifies when there are unpaid invoices due today', function () {
    $card = FinancialCreditCard::factory()->create();

    $invoice = FinancialCreditCardInvoice::factory()->for($card, 'creditCard')->create([
        'closing_date' => Carbon::today()->subDays(5),
        'due_date' => Carbon::today(),
        'reference_month' => Carbon::today()->subDays(5)->startOfMonth(),
        'paid_at' => null,
        'amount_paid' => 0,
    ]);

    FinancialTransaction::factory()->create([
        'financial_credit_card_invoice_id' => $invoice->id,
        'type' => 'expense',
        'amount' => 500.00,
        'date' => Carbon::today()->subDays(5),
        'status' => TransactionStatus::Pending,
    ]);

    $this->artisan('finance:due-today-alerts')->assertSuccessful();

    Notification::assertSentTo($this->user, GeneralNotification::class, function ($notification) {
        return $notification->level === NotificationLevel::Warning;
    });
});

it('does not notify when no vencimentos exist in the period', function () {
    $account = FinancialAccount::factory()->create();

    // Expense due in 5 days (out of the today/tomorrow window)
    FinancialTransaction::factory()->create([
        'financial_account_id' => $account->id,
        'type' => 'expense',
        'date' => Carbon::today()->addDays(5),
        'status' => TransactionStatus::Pending,
    ]);

    $this->artisan('finance:due-today-alerts')->assertSuccessful();

    Notification::assertNothingSent();
});

it('does not count posted transactions as due', function () {
    $account = FinancialAccount::factory()->create();

    // Posted expense due today – already settled, should not trigger
    FinancialTransaction::factory()->create([
        'financial_account_id' => $account->id,
        'type' => 'expense',
        'date' => Carbon::today(),
        'status' => TransactionStatus::Posted,
    ]);

    $this->artisan('finance:due-today-alerts')->assertSuccessful();

    Notification::assertNothingSent();
});

it('does not count paid invoices as due', function () {
    $card = FinancialCreditCard::factory()->create();

    $invoice = FinancialCreditCardInvoice::factory()->for($card, 'creditCard')->create([
        'closing_date' => Carbon::today()->subDays(5),
        'due_date' => Carbon::today(),
        'reference_month' => Carbon::today()->subDays(5)->startOfMonth(),
        'paid_at' => Carbon::yesterday(),
    ]);

    FinancialTransaction::factory()->create([
        'financial_credit_card_invoice_id' => $invoice->id,
        'type' => 'expense',
        'amount' => 500.00,
        'date' => Carbon::today()->subDays(5),
        'status' => TransactionStatus::Posted,
    ]);

    $this->artisan('finance:due-today-alerts')->assertSuccessful();

    Notification::assertNothingSent();
});

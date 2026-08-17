<?php

use App\Enums\NotificationLevel;
use App\Enums\TransactionStatus;
use App\Models\FinancialAccount;
use App\Models\FinancialTransaction;
use App\Models\User;
use App\Notifications\GeneralNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    $this->user = User::factory()->create();
    Notification::fake();

    // All tests run on the 1st of a month so monthly-digest targets the previous month
    Carbon::setTestNow(Carbon::create(2026, 8, 1, 9, 0, 0));
});

afterEach(function () {
    Carbon::setTestNow(null);
});

it('sends a monthly digest with income, expense and net result', function () {
    $account = FinancialAccount::factory()->create();

    // Transactions in previous month (July 2026)
    FinancialTransaction::factory()->create([
        'financial_account_id' => $account->id,
        'type' => 'income',
        'amount' => 3000.00,
        'date' => '2026-07-15',
        'status' => TransactionStatus::Posted,
    ]);

    FinancialTransaction::factory()->create([
        'financial_account_id' => $account->id,
        'type' => 'expense',
        'amount' => 1200.00,
        'date' => '2026-07-20',
        'status' => TransactionStatus::Posted,
    ]);

    $this->artisan('finance:monthly-digest')->assertSuccessful();

    Notification::assertSentTo($this->user, GeneralNotification::class, function ($notification) {
        return str_contains($notification->title, 'Julho De 2026')
            && isset($notification->details['Receitas'])
            && isset($notification->details['Despesas'])
            && isset($notification->details['Resultado Líquido'])
            && $notification->actionUrl === route('financial.dashboard');
    });
});

it('ignores drafts in the monthly digest', function () {
    FinancialTransaction::factory()->create([
        'type' => 'expense',
        'amount' => 9000,
        'date' => '2026-07-20',
        'status' => TransactionStatus::Draft,
    ]);

    $this->artisan('finance:monthly-digest')->assertSuccessful();

    Notification::assertSentTo($this->user, GeneralNotification::class, function ($notification) {
        return $notification->details['Despesas'] === formatCurrency(0)
            && $notification->details['Resultado Líquido'] === formatCurrency(0);
    });
});

it('uses Success level when net result is positive', function () {
    $account = FinancialAccount::factory()->create();

    FinancialTransaction::factory()->create([
        'financial_account_id' => $account->id,
        'type' => 'income',
        'amount' => 5000.00,
        'date' => '2026-07-10',
        'status' => TransactionStatus::Posted,
    ]);

    FinancialTransaction::factory()->create([
        'financial_account_id' => $account->id,
        'type' => 'expense',
        'amount' => 2000.00,
        'date' => '2026-07-10',
        'status' => TransactionStatus::Posted,
    ]);

    $this->artisan('finance:monthly-digest')->assertSuccessful();

    Notification::assertSentTo($this->user, GeneralNotification::class, function ($notification) {
        return $notification->level === NotificationLevel::Success;
    });
});

it('uses Info level when net result is negative', function () {
    $account = FinancialAccount::factory()->create();

    FinancialTransaction::factory()->create([
        'financial_account_id' => $account->id,
        'type' => 'income',
        'amount' => 1000.00,
        'date' => '2026-07-10',
        'status' => TransactionStatus::Posted,
    ]);

    FinancialTransaction::factory()->create([
        'financial_account_id' => $account->id,
        'type' => 'expense',
        'amount' => 3000.00,
        'date' => '2026-07-10',
        'status' => TransactionStatus::Posted,
    ]);

    $this->artisan('finance:monthly-digest')->assertSuccessful();

    Notification::assertSentTo($this->user, GeneralNotification::class, function ($notification) {
        return $notification->level === NotificationLevel::Info;
    });
});

it('sends a zero-balance digest even when no transactions exist in the previous month', function () {
    $this->artisan('finance:monthly-digest')->assertSuccessful();

    // Should still send (zero income / zero expense)
    Notification::assertSentTo($this->user, GeneralNotification::class, function ($notification) {
        return $notification->level === NotificationLevel::Success; // 0 - 0 = 0 >= 0
    });
});

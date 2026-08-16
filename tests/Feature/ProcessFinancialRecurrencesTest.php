<?php

use App\Enums\NotificationLevel;
use App\Models\FinancialAccount;
use App\Models\FinancialRecurrence;
use App\Models\User;
use App\Notifications\GeneralNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    $this->user = User::factory()->create();
    Notification::fake();
});

it('notifies users when recurrences are processed with count and total', function () {
    $account = FinancialAccount::factory()->create();

    FinancialRecurrence::factory()->create([
        'financial_account_id' => $account->id,
        'type' => 'expense',
        'amount' => 100.00,
        'frequency' => 'monthly',
        'is_active' => true,
        'start_date' => Carbon::today()->subMonth(),
        'next_processing_date' => Carbon::today(),
    ]);

    $this->artisan('finance:process-recurrences')->assertSuccessful();

    Notification::assertSentTo($this->user, GeneralNotification::class, function ($notification) {
        return $notification->title === 'Recorrências Processadas'
            && $notification->level === NotificationLevel::Info
            && isset($notification->details['Quantidade'])
            && isset($notification->details['Valor Total']);
    });
});

it('does not notify when no eligible recurrences exist', function () {
    // Recurrence with next_processing_date in the future
    FinancialRecurrence::factory()->create([
        'is_active' => true,
        'next_processing_date' => Carbon::today()->addMonth(),
        'start_date' => Carbon::today()->subMonth(),
    ]);

    $this->artisan('finance:process-recurrences')->assertSuccessful();

    Notification::assertNothingSent();
});

it('does not notify when all recurrences are inactive', function () {
    FinancialRecurrence::factory()->create([
        'is_active' => false,
        'next_processing_date' => Carbon::today(),
        'start_date' => Carbon::today()->subMonth(),
    ]);

    $this->artisan('finance:process-recurrences')->assertSuccessful();

    Notification::assertNothingSent();
});

it('includes aggregated total amount in the notification details', function () {
    $account = FinancialAccount::factory()->create();

    FinancialRecurrence::factory()->count(2)->create([
        'financial_account_id' => $account->id,
        'type' => 'expense',
        'amount' => 50.00,
        'frequency' => 'monthly',
        'is_active' => true,
        'start_date' => Carbon::today()->subMonth(),
        'next_processing_date' => Carbon::today(),
    ]);

    $this->artisan('finance:process-recurrences')->assertSuccessful();

    Notification::assertSentTo($this->user, GeneralNotification::class, function ($notification) {
        return str_contains($notification->details['Quantidade'], '2')
            && str_contains($notification->details['Valor Total'], '100');
    });
});

it('lists each processed recurrence item in notification details', function () {
    $account = FinancialAccount::factory()->create(['name' => 'Itaú']);

    FinancialRecurrence::factory()->create([
        'title' => 'Assinatura Netflix',
        'financial_account_id' => $account->id,
        'type' => 'expense',
        'amount' => 55.90,
        'frequency' => 'monthly',
        'is_active' => true,
        'start_date' => Carbon::today()->subMonth(),
        'next_processing_date' => Carbon::today(),
    ]);

    $this->artisan('finance:process-recurrences')->assertSuccessful();

    Notification::assertSentTo($this->user, GeneralNotification::class, function ($notification) {
        return isset($notification->details['1. Assinatura Netflix'])
            && str_contains($notification->details['1. Assinatura Netflix'], '55,90')
            && str_contains($notification->details['1. Assinatura Netflix'], 'Conta Itaú');
    });
});

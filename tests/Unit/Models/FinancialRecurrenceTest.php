<?php

use App\Enums\FinancialAccountType;
use App\Models\FinancialAccount;
use App\Models\FinancialCreditCard;
use App\Models\FinancialRecurrence;

it('scopes forAccounts correctly filters recurrences', function () {
    $account1 = FinancialAccount::factory()->create();
    $account2 = FinancialAccount::factory()->create();
    $account3 = FinancialAccount::factory()->create();

    // Direct account recurrence
    $r1 = FinancialRecurrence::factory()->create(['financial_account_id' => $account1->id, 'title' => 'Test 1', 'type' => 'income', 'amount' => 100, 'start_date' => now()->format('Y-m-d'), 'next_processing_date' => now()->format('Y-m-d')]);
    $r2 = FinancialRecurrence::factory()->create(['financial_account_id' => $account2->id, 'title' => 'Test 2', 'type' => 'expense', 'amount' => 50, 'start_date' => now()->format('Y-m-d'), 'next_processing_date' => now()->format('Y-m-d')]);

    // Credit card recurrence
    $card = FinancialCreditCard::factory()->create(['financial_account_id' => $account3->id]);
    $r3 = FinancialRecurrence::factory()->create([
        'financial_account_id' => null,
        'financial_credit_card_id' => $card->id,
        'title' => 'Test 3',
        'type' => 'expense',
        'amount' => 20,
        'start_date' => now()->format('Y-m-d'),
        'next_processing_date' => now()->format('Y-m-d'),
    ]);

    // Filter by $account1 and $account3
    $result = FinancialRecurrence::forAccounts([$account1->id, $account3->id])->get();

    expect($result)->toHaveCount(2)
        ->and($result->pluck('id')->toArray())->toContain($r1->id, $r3->id)
        ->and($result->pluck('id')->toArray())->not->toContain($r2->id);
});

it('scopes withoutInvestments excludes investment accounts in recurrences', function () {
    $checkingAccount = FinancialAccount::factory()->create(['type' => FinancialAccountType::Checking]);
    $investmentAccount = FinancialAccount::factory()->create(['type' => FinancialAccountType::Investment]);

    $r1 = FinancialRecurrence::factory()->create(['financial_account_id' => $checkingAccount->id, 'title' => 'Test 1', 'type' => 'income', 'amount' => 100, 'start_date' => now()->format('Y-m-d'), 'next_processing_date' => now()->format('Y-m-d')]);
    $r2 = FinancialRecurrence::factory()->create(['financial_account_id' => $investmentAccount->id, 'title' => 'Test 2', 'type' => 'expense', 'amount' => 50, 'start_date' => now()->format('Y-m-d'), 'next_processing_date' => now()->format('Y-m-d')]);

    $result = FinancialRecurrence::withoutInvestments()->get();

    expect($result)->toHaveCount(1)
        ->and($result->first()->id)->toBe($r1->id);
});

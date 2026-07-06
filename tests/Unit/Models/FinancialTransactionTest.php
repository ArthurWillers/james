<?php

use App\Enums\FinancialAccountType;
use App\Models\FinancialAccount;
use App\Models\FinancialCreditCard;
use App\Models\FinancialCreditCardInvoice;
use App\Models\FinancialTag;
use App\Models\FinancialTransaction;

it('scopes forAccounts correctly filters transactions', function () {
    $account1 = FinancialAccount::factory()->create();
    $account2 = FinancialAccount::factory()->create();
    $account3 = FinancialAccount::factory()->create();

    // Direct account transaction
    $t1 = FinancialTransaction::factory()->create(['financial_account_id' => $account1->id]);
    $t2 = FinancialTransaction::factory()->create(['financial_account_id' => $account2->id]);

    // Credit card transaction
    $card = FinancialCreditCard::factory()->create(['financial_account_id' => $account3->id]);
    $invoice = FinancialCreditCardInvoice::factory()->create(['financial_credit_card_id' => $card->id]);
    $t3 = FinancialTransaction::factory()->create([
        'financial_account_id' => null,
        'financial_credit_card_invoice_id' => $invoice->id,
    ]);

    // Filter by $account1 and $account3
    $result = FinancialTransaction::forAccounts([$account1->id, $account3->id])->get();

    expect($result)->toHaveCount(2)
        ->and($result->pluck('id')->toArray())->toContain($t1->id, $t3->id)
        ->and($result->pluck('id')->toArray())->not->toContain($t2->id);
});

it('scopes withoutInvestments excludes investment accounts', function () {
    $checkingAccount = FinancialAccount::factory()->create(['type' => FinancialAccountType::Checking]);
    $investmentAccount = FinancialAccount::factory()->create(['type' => FinancialAccountType::Investment]);

    $t1 = FinancialTransaction::factory()->create(['financial_account_id' => $checkingAccount->id]);
    $t2 = FinancialTransaction::factory()->create(['financial_account_id' => $investmentAccount->id]);

    $result = FinancialTransaction::withoutInvestments()->get();

    expect($result)->toHaveCount(1)
        ->and($result->first()->id)->toBe($t1->id);
});

it('scopes withoutPartialPayments correctly filters out partial payments tags', function () {
    $t1 = FinancialTransaction::factory()->create();
    $t2 = FinancialTransaction::factory()->create();

    $tag = FinancialTag::factory()->create([
        'id' => FinancialTag::PAGAMENTO_PARCIAL_ID,
        'name' => 'Pagamento Parcial',
        'color_hex' => '#000000',
    ]);

    $t2->tags()->attach($tag->id, ['is_primary' => true]);

    $result = FinancialTransaction::withoutPartialPayments()->get();

    expect($result)->toHaveCount(1)
        ->and($result->first()->id)->toBe($t1->id);
});

it('retrieves transfer pair correctly', function () {
    $t1 = FinancialTransaction::factory()->create();
    $t1->update(['transfer_pair_id' => $t1->id]);

    $t2 = FinancialTransaction::factory()->create(['transfer_pair_id' => $t1->id]);

    expect($t1->transfer_pair->id)->toBe($t2->id)
        ->and($t2->transfer_pair->id)->toBe($t1->id);
});

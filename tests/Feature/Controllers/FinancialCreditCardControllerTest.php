<?php

use App\Models\FinancialAccount;
use App\Models\FinancialCreditCard;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('can list credit cards', function () {
    FinancialCreditCard::factory()->count(3)->create();

    $this->get(route('financial.cards.index'))
        ->assertSuccessful()
        ->assertViewIs('finance.cards.index');
});

it('can view create credit card page', function () {
    $this->get(route('financial.cards.create'))
        ->assertSuccessful()
        ->assertViewIs('finance.cards.create');
});

it('can store credit card', function () {
    $account = FinancialAccount::factory()->create();

    $data = [
        'name' => 'Meu Cartão Black',
        'financial_account_id' => $account->id,
        'credit_limit' => 15000.00,
        'closing_day' => 10,
        'due_day' => 15,
    ];

    $this->post(route('financial.cards.store'), $data)
        ->assertRedirect(route('financial.cards.show', FinancialCreditCard::latest('id')->first()));

    $this->assertDatabaseHas('financial_credit_cards', [
        'name' => 'Meu Cartão Black',
        'closing_day' => 10,
    ]);
});

it('can view edit credit card page', function () {
    $card = FinancialCreditCard::factory()->create();

    $this->get(route('financial.cards.edit', $card))
        ->assertSuccessful()
        ->assertViewIs('finance.cards.edit');
});

it('can update credit card', function () {
    $card = FinancialCreditCard::factory()->create();

    $data = [
        'name' => 'Cartão Platinum',
        'financial_account_id' => $card->financial_account_id,
        'credit_limit' => 20000.00,
        'closing_day' => 5,
        'due_day' => 12,
    ];

    $this->put(route('financial.cards.update', $card), $data)
        ->assertRedirect(route('financial.cards.show', $card));

    $this->assertDatabaseHas('financial_credit_cards', [
        'id' => $card->id,
        'name' => 'Cartão Platinum',
        'due_day' => 12,
    ]);
});

it('can soft delete credit card', function () {
    $card = FinancialCreditCard::factory()->create();

    $this->delete(route('financial.cards.destroy', $card))
        ->assertRedirect(route('financial.cards.index'));

    $this->assertSoftDeleted($card);
});

it('can list trashed credit cards', function () {
    FinancialCreditCard::factory()->count(2)->trashed()->create();

    $this->get(route('financial.cards.trashed'))
        ->assertSuccessful()
        ->assertViewIs('finance.cards.trashed');
});

it('can restore trashed credit card', function () {
    $card = FinancialCreditCard::factory()->trashed()->create();

    $this->patch(route('financial.cards.restore', $card))
        ->assertRedirect(route('financial.cards.show', $card));

    $this->assertNotSoftDeleted($card);
});

it('can force delete credit card', function () {
    $card = FinancialCreditCard::factory()->trashed()->create();

    $this->delete(route('financial.cards.forceDestroy', $card))
        ->assertRedirect(route('financial.cards.trashed'));

    $this->assertDatabaseMissing('financial_credit_cards', [
        'id' => $card->id,
    ]);
});

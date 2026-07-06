<?php

use App\Models\FinancialAccount;
use App\Models\FinancialTag;
use App\Models\FinancialTransaction;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('can list transactions', function () {
    FinancialTransaction::factory()->count(3)->create();

    $this->get(route('financial.transactions.index'))
        ->assertSuccessful()
        ->assertViewIs('finance.transactions.index');
});

it('can view create transaction page', function () {
    $this->get(route('financial.transactions.create'))
        ->assertSuccessful()
        ->assertViewIs('finance.transactions.create');
});

it('can store transaction', function () {
    $account = FinancialAccount::factory()->create();

    $data = [
        'mode' => 'single',
        'targetType' => 'account',
        'financial_account_id' => $account->id,
        'type' => 'expense',
        'amount' => 125.50,
        'description' => 'Compra no supermercado',
        'date' => now()->format('Y-m-d'),
        'is_posted' => true,
    ];

    $this->post(route('financial.transactions.store'), $data)
        ->assertRedirect(route('financial.transactions.index'));

    $this->assertDatabaseHas('financial_transactions', [
        'amount' => 125.50,
        'description' => 'Compra no supermercado',
    ]);
});

it('can view edit transaction page', function () {
    $transaction = FinancialTransaction::factory()->create();

    $this->get(route('financial.transactions.edit', $transaction))
        ->assertSuccessful()
        ->assertViewIs('finance.transactions.edit');
});

it('can update transaction', function () {
    $account = FinancialAccount::factory()->create();
    $transaction = FinancialTransaction::factory()->create([
        'financial_account_id' => $account->id,
    ]);

    $data = [
        'mode' => 'single',
        'targetType' => 'account',
        'financial_account_id' => $account->id,
        'type' => 'income',
        'amount' => 300.00,
        'description' => 'Venda de bicicleta',
        'date' => now()->format('Y-m-d'),
        'is_posted' => false,
    ];

    $this->put(route('financial.transactions.update', $transaction), $data)
        ->assertRedirect(route('financial.transactions.show', $transaction));

    $this->assertDatabaseHas('financial_transactions', [
        'id' => $transaction->id,
        'type' => 'income',
        'amount' => 300.00,
    ]);
});

it('can soft delete transaction', function () {
    $transaction = FinancialTransaction::factory()->create();

    $this->delete(route('financial.transactions.destroy', $transaction))
        ->assertRedirect(route('financial.transactions.index'));

    $this->assertSoftDeleted($transaction);
});

it('can list trashed transactions', function () {
    FinancialTransaction::factory()->count(2)->trashed()->create();

    $this->get(route('financial.transactions.trashed'))
        ->assertSuccessful()
        ->assertViewIs('finance.transactions.trashed');
});

it('can restore trashed transaction', function () {
    $transaction = FinancialTransaction::factory()->trashed()->create();

    $this->from(route('financial.transactions.trashed'))
        ->patch(route('financial.transactions.restore', $transaction))
        ->assertRedirect(route('financial.transactions.trashed'));

    $this->assertNotSoftDeleted($transaction);
});

it('can force delete transaction', function () {
    $transaction = FinancialTransaction::factory()->trashed()->create();

    $this->from(route('financial.transactions.trashed'))
        ->delete(route('financial.transactions.forceDestroy', $transaction))
        ->assertRedirect(route('financial.transactions.trashed'));

    $this->assertDatabaseMissing('financial_transactions', [
        'id' => $transaction->id,
    ]);
});

it('can store a transfer between accounts', function () {
    FinancialTag::factory()->create([
        'id' => FinancialTag::TRANSFERENCIA_ID,
        'name' => 'Transferência',
        'is_protected' => true,
    ]);

    $accountFrom = FinancialAccount::factory()->create();
    $accountTo = FinancialAccount::factory()->create();

    $data = [
        'from_account_id' => $accountFrom->id,
        'to_account_id' => $accountTo->id,
        'amount' => 500.00,
        'date' => now()->format('Y-m-d'),
        'description' => 'Transferência para poupança',
    ];

    $this->post(route('financial.transactions.transfer.store'), $data)
        ->assertRedirect();

    $this->assertDatabaseHas('financial_transactions', [
        'financial_account_id' => $accountFrom->id,
        'type' => 'expense',
        'amount' => 500.00,
    ]);

    $this->assertDatabaseHas('financial_transactions', [
        'financial_account_id' => $accountTo->id,
        'type' => 'income',
        'amount' => 500.00,
    ]);
});

<?php

use App\Enums\TransactionStatus;
use App\Jobs\ScrapeNfceInvoiceJob;
use App\Models\FinancialAccount;
use App\Models\FinancialCreditCard;
use App\Models\FinancialTag;
use App\Models\FinancialTransaction;
use App\Models\User;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\URL;

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
        ->assertViewIs('finance.transactions.create')
        ->assertSee('Importar NFC-e')
        ->assertSee('Colar URL')
        ->assertSee(route('financial.transactions.nfce.import'), false)
        ->assertSee('name="url"', false)
        ->assertSee('h-11', false);
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
        'status' => 'posted',
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

it('shows the imported nfce portal and formatted issuer document', function () {
    $transaction = FinancialTransaction::factory()->create([
        'nfce_source_url' => 'https://dfe-portal.svrs.rs.gov.br/Dfe/QrCodeNFce?p=43111111111111111111111111111111111111111111%7C3%7C1',
        'nfce_issuer_document' => '12345678000195',
    ]);

    $this->get(route('financial.transactions.show', $transaction))
        ->assertSuccessful()
        ->assertSee('Dados da NFC-e')
        ->assertSee('Abrir NFC-e')
        ->assertSee('https://dfe-portal.svrs.rs.gov.br/Dfe/QrCodeNFce?p=', false)
        ->assertSee('12.345.678/0001-95');
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
        'status' => 'pending',
    ];

    $this->put(route('financial.transactions.update', $transaction), $data)
        ->assertRedirect(route('financial.transactions.show', $transaction));

    $this->assertDatabaseHas('financial_transactions', [
        'id' => $transaction->id,
        'type' => 'income',
        'amount' => 300.00,
    ]);
});

it('finalizes an imported draft as posted on an account', function () {
    $account = FinancialAccount::factory()->create();
    $transaction = FinancialTransaction::factory()->create([
        'status' => TransactionStatus::Draft,
        'nfce_access_key' => str_repeat('1', 44),
    ]);

    $this->put(route('financial.transactions.update', $transaction), [
        'targetType' => 'account',
        'financial_account_id' => $account->id,
        'type' => 'expense',
        'amount' => 125.69,
        'description' => 'Compra importada',
        'date' => '2026-08-17',
        'status' => TransactionStatus::Posted->value,
    ])->assertRedirect(route('financial.transactions.show', $transaction));

    expect($transaction->refresh())
        ->financial_account_id->toBe($account->id)
        ->financial_credit_card_invoice_id->toBeNull()
        ->status->toBe(TransactionStatus::Posted);
});

it('finalizes an imported draft as pending on an account', function () {
    $account = FinancialAccount::factory()->create();
    $transaction = FinancialTransaction::factory()->create([
        'status' => TransactionStatus::Draft,
        'nfce_access_key' => str_repeat('2', 44),
    ]);

    $this->put(route('financial.transactions.update', $transaction), [
        'targetType' => 'account',
        'financial_account_id' => $account->id,
        'type' => 'expense',
        'amount' => 125.69,
        'description' => 'Compra importada',
        'date' => '2026-08-17',
        'status' => TransactionStatus::Pending->value,
    ])->assertRedirect(route('financial.transactions.show', $transaction));

    expect($transaction->refresh()->status)->toBe(TransactionStatus::Pending);
});

it('assigns an imported draft to the correct invoice as pending', function () {
    $account = FinancialAccount::factory()->create();
    $card = FinancialCreditCard::factory()->create([
        'financial_account_id' => $account->id,
        'closing_day' => 10,
        'due_day' => 15,
    ]);
    $transaction = FinancialTransaction::factory()->create([
        'status' => TransactionStatus::Draft,
        'nfce_access_key' => str_repeat('3', 44),
    ]);

    $this->put(route('financial.transactions.update', $transaction), [
        'targetType' => 'card',
        'financial_credit_card_id' => $card->id,
        'type' => 'expense',
        'amount' => 125.69,
        'description' => 'Compra importada',
        'date' => '2026-08-11',
    ])->assertRedirect(route('financial.transactions.show', $transaction));

    $transaction->refresh()->load('invoice');

    expect($transaction)
        ->financial_account_id->toBeNull()
        ->status->toBe(TransactionStatus::Pending)
        ->and($transaction->invoice->financial_credit_card_id)->toBe($card->id)
        ->and($transaction->invoice->reference_month->format('Y-m'))->toBe('2026-09');
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

it('requires authentication to request an NFC-e import', function () {
    auth()->logout();

    $this->post(route('financial.transactions.nfce.import'), ['url' => nfceImportUrl()])
        ->assertRedirect(route('login'));
});

it('validates the NFC-e import URL', function () {
    Bus::fake();

    $this->post(route('financial.transactions.nfce.import'), [])
        ->assertSessionHasErrors('url');

    Bus::assertNothingDispatched();
});

it('rejects unsupported NFC-e URLs', function () {
    Bus::fake();

    $this->from(route('financial.transactions.create'))
        ->post(route('financial.transactions.nfce.import'), ['url' => 'https://untrusted.example.test/nfce?p=43111111111111111111111111111111111111111111'])
        ->assertRedirect(route('financial.transactions.create'))
        ->assertSessionHasErrors('url');

    Bus::assertNothingDispatched();
});

it('rejects NFC-e access keys that already exist, including trashed transactions', function () {
    Bus::fake();
    FinancialTransaction::factory()
        ->nfce('43111111111111111111111111111111111111111111')
        ->trashed()
        ->create();

    $this->from(route('financial.transactions.create'))
        ->post(route('financial.transactions.nfce.import'), ['url' => nfceImportUrl()])
        ->assertRedirect(route('financial.transactions.create'))
        ->assertSessionHasErrors('url');

    Bus::assertNothingDispatched();
});

it('dispatches the NFC-e import job and redirects immediately', function () {
    Bus::fake();

    $this->post(route('financial.transactions.nfce.import'), ['url' => nfceImportUrl()])
        ->assertRedirect(route('financial.transactions.index'))
        ->assertSessionHas('success', 'Importação enviada para processamento. Você será notificado quando terminar.');

    Bus::assertDispatched(ScrapeNfceInvoiceJob::class, function (ScrapeNfceInvoiceJob $job): bool {
        return $job->requesterId === $this->user->id
            && $job->provider === 'svrs'
            && $job->accessKey === '43111111111111111111111111111111111111111111'
            && $job->uf === 'RS'
            && $job->sourceEndpoint === 'https://dfe-portal.svrs.rs.gov.br/Dfe/QrCodeNFce'
            && $job->requestParameterSuffix === '|3|1'
            && ! str_contains(serialize($job), nfceImportUrl());
    });
});

it('retries an NFC-e import from a signed notification action', function () {
    Bus::fake();

    $this->get(nfceRetryUrl($this->user))
        ->assertRedirect(route('notifications.index'))
        ->assertSessionHas('success', 'Importação reenviada para processamento. Você será notificado quando terminar.');

    Bus::assertDispatched(ScrapeNfceInvoiceJob::class, function (ScrapeNfceInvoiceJob $job): bool {
        return $job->requesterId === $this->user->id
            && $job->provider === 'svrs'
            && $job->accessKey === '43111111111111111111111111111111111111111111'
            && $job->uf === 'RS'
            && $job->sourceEndpoint === 'https://dfe-portal.svrs.rs.gov.br/Dfe/QrCodeNFce'
            && $job->requestParameterSuffix === '|3|1';
    });
});

it('rejects NFC-e retries with an invalid signature', function () {
    $this->get(route('financial.transactions.nfce.retry', [
        'payload' => Crypt::encrypt([]),
    ]))->assertForbidden();
});

it('rejects NFC-e retries with an invalid encrypted payload', function () {
    $url = URL::signedRoute('financial.transactions.nfce.retry', [
        'payload' => 'invalid',
    ]);

    $this->get($url)->assertNotFound();
});

it('rejects NFC-e retries requested by another user', function () {
    Bus::fake();
    $otherUser = User::factory()->create();

    $this->get(nfceRetryUrl($otherUser))->assertForbidden();

    Bus::assertNothingDispatched();
});

it('does not retry an NFC-e that is already imported', function () {
    Bus::fake();
    FinancialTransaction::factory()
        ->nfce('43111111111111111111111111111111111111111111')
        ->create();

    $this->get(nfceRetryUrl($this->user))
        ->assertRedirect(route('notifications.index'))
        ->assertSessionHas('success', 'Esta NFC-e já foi importada.');

    Bus::assertNothingDispatched();
});

function nfceImportUrl(): string
{
    return 'https://dfe-portal.svrs.rs.gov.br/Dfe/QrCodeNFce?p=43111111111111111111111111111111111111111111%7C3%7C1';
}

function nfceRetryUrl(User $requester): string
{
    return URL::signedRoute('financial.transactions.nfce.retry', [
        'payload' => Crypt::encrypt([
            'requester_id' => $requester->id,
            'provider' => 'svrs',
            'access_key' => '43111111111111111111111111111111111111111111',
            'uf' => 'RS',
            'source_endpoint' => 'https://dfe-portal.svrs.rs.gov.br/Dfe/QrCodeNFce',
            'request_parameter_suffix' => '|3|1',
        ]),
    ]);
}

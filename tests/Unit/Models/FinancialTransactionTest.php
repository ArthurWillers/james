<?php

use App\Enums\FinancialAccountType;
use App\Enums\TransactionStatus;
use App\Models\FinancialAccount;
use App\Models\FinancialCreditCard;
use App\Models\FinancialCreditCardInvoice;
use App\Models\FinancialTag;
use App\Models\FinancialTransaction;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;

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

it('casts status to TransactionStatus enum and filters correctly with scopes', function () {
    $posted = FinancialTransaction::factory()->posted()->create();
    $pending = FinancialTransaction::factory()->pending()->create();
    $draft = FinancialTransaction::factory()->draft()->create();

    expect($posted->status)->toBe(TransactionStatus::Posted)
        ->and($pending->status)->toBe(TransactionStatus::Pending)
        ->and($draft->status)->toBe(TransactionStatus::Draft);

    expect(FinancialTransaction::posted()->pluck('id'))->toContain($posted->id)
        ->and(FinancialTransaction::posted()->pluck('id'))->not->toContain($pending->id, $draft->id);

    expect(FinancialTransaction::pending()->pluck('id'))->toContain($pending->id)
        ->and(FinancialTransaction::pending()->pluck('id'))->not->toContain($posted->id, $draft->id);

    expect(FinancialTransaction::draft()->pluck('id'))->toContain($draft->id)
        ->and(FinancialTransaction::draft()->pluck('id'))->not->toContain($posted->id, $pending->id);
});

it('stores nfce metadata through the factory state', function () {
    $transaction = FinancialTransaction::factory()->nfce()->create();

    expect($transaction->nfce_access_key)->toHaveLength(44)
        ->and($transaction->type)->toBe('expense')
        ->and($transaction->status)->toBe(TransactionStatus::Draft)
        ->and($transaction->nfce_issuer_document)->toBe('12345678000195')
        ->and($transaction->nfce_source_url)->toContain('?p=')
        ->and($transaction->nfceSourceUrl())->toContain('%7C3%7C1');
});

it('prevents duplicate nfce access keys at the database level', function () {
    $accessKey = '43260702247794000207650100003711221171005935';
    $transaction = FinancialTransaction::factory()->nfce($accessKey)->create();

    expect(fn () => DB::transaction(function () {
        FinancialTransaction::factory()->nfce('43260702247794000207650100003711221171005935')->create();
    }))->toThrow(QueryException::class);

    $this->assertModelExists($transaction);
});

it('logs both transactions when a transfer pair is deleted', function () {
    $pairId = 999;
    $from = FinancialTransaction::factory()->create(['transfer_pair_id' => $pairId]);
    $to = FinancialTransaction::factory()->create(['transfer_pair_id' => $pairId]);

    $from->delete();

    expect(Activity::query()
        ->where('subject_type', FinancialTransaction::class)
        ->where('event', 'deleted')
        ->whereIn('subject_id', [$from->id, $to->id])
        ->count())->toBe(2);
});

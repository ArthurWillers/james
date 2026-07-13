<?php

use App\Enums\SettlementType;
use App\Models\Contact;
use App\Models\FinancialAccount;
use App\Models\FinancialTag;
use App\Models\SettlementGroup;
use App\Models\User;
use App\Services\SettlementGroupService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
    $this->service = new SettlementGroupService;
    Model::unguard();
    FinancialTag::firstOrCreate(
        ['id' => FinancialTag::REEMBOLSO_ID],
        ['name' => 'Reembolso', 'color_hex' => '#000000', 'icon' => 'heroicon-o-tag']
    );
    Model::reguard();
});

it('creates a settlement group and children correctly without financial transaction', function () {
    $contact1 = Contact::factory()->create();
    $contact2 = Contact::factory()->create();

    $validated = [
        'description' => 'Test Dinner',
        'total_amount' => 300,
        'date' => '2023-01-01',
        'mode' => 'equal',
        'my_amount' => 100,
        'create_transaction' => false,
        'contacts' => [
            ['id' => $contact1->id, 'amount' => 100],
            ['id' => $contact2->id, 'amount' => 100],
        ],
    ];

    $group = $this->service->storeGroup($validated);

    expect($group)->toBeInstanceOf(SettlementGroup::class)
        ->and($group->description)->toBe('Test Dinner')
        ->and($group->total_amount)->toBe(300.0)
        ->and($group->financial_transaction_id)->toBeNull();

    $settlements = $group->settlements;
    expect($settlements)->toHaveCount(2)
        ->and($settlements->pluck('contact_id')->toArray())->toEqualCanonicalizing([$contact1->id, $contact2->id])
        ->and($settlements->pluck('amount')->toArray())->toEqual([100.0, 100.0])
        ->and($settlements->pluck('type')->unique()->toArray())->toEqual([SettlementType::TheyOwe]);
});

it('creates a settlement group with a financial transaction and tags', function () {
    $contact = Contact::factory()->create();
    $account = FinancialAccount::factory()->create();

    // Explicitly set ID to avoid PostgreSQL sequence conflicts with ID 1
    $tag = FinancialTag::factory()->create(['id' => 10]);

    $validated = [
        'description' => 'Test Lunch',
        'total_amount' => 100,
        'date' => '2023-01-01',
        'mode' => 'custom',
        'my_amount' => 40,
        'create_transaction' => true,
        'targetType' => 'account',
        'financial_account_id' => $account->id,
        'tags' => [$tag->id],
        'primary_tag_id' => $tag->id,
        'contacts' => [
            ['id' => $contact->id, 'amount' => 60],
        ],
    ];

    $group = $this->service->storeGroup($validated);

    expect($group->financial_transaction_id)->not->toBeNull();

    $transaction = $group->financialTransaction;
    $transaction->load('items.tags');
    expect($transaction->amount)->toEqual(100.0)
        ->and($transaction->financial_account_id)->toBe($account->id)
        ->and($transaction->items)->toHaveCount(2);

    $myItem = $transaction->items->where('description', 'Minha Parte')->first();
    expect($myItem->total)->toEqual(40.0)
        ->and($myItem->tags->pluck('id')->toArray())->toContain($tag->id);

    $contactItem = $transaction->items->where('description', $contact->name)->first();
    expect($contactItem->total)->toEqual(60.0)
        ->and($contactItem->tags->pluck('id')->toArray())->toContain(FinancialTag::REEMBOLSO_ID);
});

it('updates a settlement group and replaces children', function () {
    $contact1 = Contact::factory()->create();
    $contact2 = Contact::factory()->create();

    $initialData = [
        'description' => 'Initial',
        'total_amount' => 100,
        'date' => '2023-01-01',
        'mode' => 'equal',
        'my_amount' => 50,
        'create_transaction' => false,
        'contacts' => [
            ['id' => $contact1->id, 'amount' => 50],
        ],
    ];

    $group = $this->service->storeGroup($initialData);
    expect($group->settlements)->toHaveCount(1);

    $updateData = [
        'description' => 'Updated',
        'total_amount' => 150,
        'date' => '2023-01-02',
        'mode' => 'equal',
        'my_amount' => 50,
        'create_transaction' => false,
        'contacts' => [
            ['id' => $contact1->id, 'amount' => 50],
            ['id' => $contact2->id, 'amount' => 50],
        ],
    ];

    $updatedGroup = $this->service->updateGroup($group, $updateData);

    expect($updatedGroup->description)->toBe('Updated')
        ->and($updatedGroup->total_amount)->toBe(150.0)
        ->and($updatedGroup->settlements)->toHaveCount(2);
});

it('destroys a settlement group and its transaction', function () {
    $contact = Contact::factory()->create();
    $account = FinancialAccount::factory()->create();

    $validated = [
        'description' => 'To Delete',
        'total_amount' => 100,
        'date' => '2023-01-01',
        'mode' => 'equal',
        'my_amount' => 50,
        'create_transaction' => true,
        'targetType' => 'account',
        'financial_account_id' => $account->id,
        'contacts' => [
            ['id' => $contact->id, 'amount' => 50],
        ],
    ];

    $group = $this->service->storeGroup($validated);
    $transactionId = $group->financial_transaction_id;

    $this->service->destroyGroup($group);

    $this->assertSoftDeleted('settlement_groups', ['id' => $group->id]);
    $this->assertSoftDeleted('settlements', ['settlement_group_id' => $group->id]);
    $this->assertDatabaseMissing('financial_transactions', ['id' => $transactionId]);
});

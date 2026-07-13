<?php

use App\Models\Contact;
use App\Models\SettlementGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('can list settlement groups', function () {
    $this->get(route('settlements.groups.index'))->assertSuccessful();
});

it('can view creation screen', function () {
    $contact = Contact::factory()->create();
    $this->get(route('settlements.groups.create', ['contacts' => $contact->id]))->assertSuccessful();
});

it('can view group details', function () {
    $group = SettlementGroup::create([
        'description' => 'Test',
        'total_amount' => 100,
        'date' => '2023-01-01',
        'mode' => 'equal',
    ]);

    $this->get(route('settlements.groups.show', $group))->assertSuccessful();
});

it('can store a settlement group', function () {
    $contact = Contact::factory()->create();

    $payload = [
        'description' => 'Pizza',
        'total_amount' => 100,
        'date' => '2023-01-01',
        'mode' => 'equal',
        'my_amount' => 50,
        'create_transaction' => false,
        'contacts' => [
            ['id' => $contact->id, 'amount' => 50],
        ],
    ];

    $this->post(route('settlements.groups.store'), $payload)
        ->assertRedirect(route('settlements.index'));

    $this->assertDatabaseHas('settlement_groups', [
        'description' => 'Pizza',
        'total_amount' => 100,
    ]);
});

it('can view edit screen', function () {
    $group = SettlementGroup::create([
        'description' => 'Test',
        'total_amount' => 100,
        'date' => '2023-01-01',
        'mode' => 'equal',
    ]);

    $this->get(route('settlements.groups.edit', $group))->assertSuccessful();
});

it('can update a settlement group', function () {
    $contact = Contact::factory()->create();
    $group = SettlementGroup::create([
        'description' => 'Test',
        'total_amount' => 100,
        'date' => '2023-01-01',
        'mode' => 'equal',
    ]);

    $payload = [
        'description' => 'Updated Pizza',
        'total_amount' => 200,
        'date' => '2023-01-02',
        'mode' => 'equal',
        'my_amount' => 100,
        'create_transaction' => false,
        'contacts' => [
            ['id' => $contact->id, 'amount' => 100],
        ],
    ];

    $this->put(route('settlements.groups.update', $group), $payload)
        ->assertRedirect(route('settlements.index'));

    $this->assertDatabaseHas('settlement_groups', [
        'id' => $group->id,
        'description' => 'Updated Pizza',
        'total_amount' => 200,
    ]);
});

it('can soft delete a settlement group', function () {
    $group = SettlementGroup::create([
        'description' => 'To Delete',
        'total_amount' => 100,
        'date' => '2023-01-01',
        'mode' => 'equal',
    ]);

    $this->delete(route('settlements.groups.destroy', $group))
        ->assertRedirect();

    $this->assertSoftDeleted('settlement_groups', ['id' => $group->id]);
});

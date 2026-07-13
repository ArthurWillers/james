<?php

use App\Enums\SettlementType;
use App\Models\Contact;
use App\Models\Settlement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('can list settlements index (dashboard)', function () {
    $this->get(route('settlements.index'))->assertSuccessful();
});

it('can list global settlement history', function () {
    $this->get(route('settlements.history'))->assertSuccessful();
});

it('can view contact settlement ledger', function () {
    $contact = Contact::factory()->create();
    $this->get(route('settlements.contact.show', $contact))->assertSuccessful();
});

it('can view settlement details', function () {
    $contact = Contact::factory()->create();
    $settlement = Settlement::create([
        'contact_id' => $contact->id,
        'type' => SettlementType::TheyOwe->value,
        'amount' => 100,
        'description' => 'Test',
        'date' => '2023-01-01',
    ]);

    $this->get(route('settlements.show_item', $settlement))->assertSuccessful();
});

it('can view creation screen', function () {
    $contact = Contact::factory()->create();
    $this->get(route('settlements.create', $contact))->assertSuccessful();
});

it('can store a settlement', function () {
    $contact = Contact::factory()->create();

    $payload = [
        'type' => SettlementType::TheyOwe->value,
        'amount' => 150,
        'description' => 'Lunch',
        'date' => '2023-01-01',
        'create_transaction' => false,
    ];

    $this->post(route('settlements.store', $contact->id), $payload)
        ->assertRedirect(route('settlements.contact.show', $contact->id));

    $this->assertDatabaseHas('settlements', [
        'contact_id' => $contact->id,
        'amount' => 150,
        'description' => 'Lunch',
    ]);
});

it('can update a settlement', function () {
    $contact = Contact::factory()->create();
    $settlement = Settlement::create([
        'contact_id' => $contact->id,
        'type' => SettlementType::TheyOwe->value,
        'amount' => 100,
        'description' => 'Test',
        'date' => '2023-01-01',
    ]);

    $payload = [
        'type' => SettlementType::TheyOwe->value,
        'amount' => 200,
        'description' => 'Updated Test',
        'date' => '2023-01-02',
        'create_transaction' => false,
    ];

    $this->put(route('settlements.update', $settlement->id), $payload)
        ->assertRedirect(route('settlements.contact.show', $contact->id));

    $this->assertDatabaseHas('settlements', [
        'id' => $settlement->id,
        'amount' => 200,
        'description' => 'Updated Test',
    ]);
});

it('can soft delete a settlement', function () {
    $contact = Contact::factory()->create();
    $settlement = Settlement::create([
        'contact_id' => $contact->id,
        'type' => SettlementType::TheyOwe->value,
        'amount' => 100,
        'description' => 'Test',
        'date' => '2023-01-01',
    ]);

    $this->delete(route('settlements.destroy', $settlement))
        ->assertRedirect();

    $this->assertSoftDeleted('settlements', ['id' => $settlement->id]);
});

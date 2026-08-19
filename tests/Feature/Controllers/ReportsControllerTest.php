<?php

use App\Models\FinancialTag;
use App\Models\FinancialTransaction;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('can view reports page', function () {
    $this->get(route('financial.reports'))
        ->assertSuccessful()
        ->assertViewIs('finance.reports');
});

it('defaults to all_time period when no period is specified', function () {
    $this->get(route('financial.reports'))
        ->assertSuccessful()
        ->assertViewHas('period', 'all_time');
});

it('respects the period query parameter', function () {
    $this->get(route('financial.reports', ['period' => 'this_month']))
        ->assertSuccessful()
        ->assertViewHas('period', 'this_month');
});

it('filters report rows by transaction and item tags before paginating', function () {
    $tag = FinancialTag::factory()->create();
    $startDate = '2026-08-18';
    $endDate = '2026-08-19';

    FinancialTransaction::factory()->posted()->count(50)->create([
        'date' => $startDate,
    ])->each(function (FinancialTransaction $transaction) use ($tag): void {
        $transaction->tags()->attach($tag, ['is_primary' => true]);
    });

    $itemTaggedTransaction = FinancialTransaction::factory()->posted()->create([
        'amount' => 10,
        'date' => $endDate,
    ]);
    $item = $itemTaggedTransaction->items()->create([
        'description' => 'Item com tag',
        'quantity' => 1,
        'unit_price' => 10,
        'total' => 10,
    ]);
    $item->tags()->attach($tag, ['is_primary' => true]);

    $this->get(route('financial.reports', [
        'period' => 'custom',
        'startDate' => $startDate,
        'endDate' => $endDate,
        'tag_id' => $tag->id,
        'page' => 2,
    ]))
        ->assertSuccessful()
        ->assertViewHas('selectedTagId', $tag->id)
        ->assertViewHas('transactions', function ($transactions) use ($item, $itemTaggedTransaction): bool {
            return $transactions->total() === 51
                && $transactions->count() === 1
                && $transactions->currentPage() === 2
                && $transactions->lastPage() === 2
                && str_contains($transactions->previousPageUrl(), 'tag_id=')
                && str_ends_with($transactions->previousPageUrl(), '#transactions-table')
                && $transactions->first()->id === $itemTaggedTransaction->id.'_item_'.$item->id;
        });
});

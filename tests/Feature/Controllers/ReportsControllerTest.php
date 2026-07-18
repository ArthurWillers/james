<?php

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

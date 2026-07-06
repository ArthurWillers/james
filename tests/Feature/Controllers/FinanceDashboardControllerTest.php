<?php

use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('can view the finance dashboard', function () {
    $this->get(route('financial.dashboard'))
        ->assertSuccessful()
        ->assertViewIs('finance.dashboard');
});

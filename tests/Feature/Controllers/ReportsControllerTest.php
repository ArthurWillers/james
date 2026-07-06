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

<?php

use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('can fetch dashboard chart data', function () {
    $this->getJson(route('financial.dashboard.chart-data', ['period' => 'this_month']))
        ->assertSuccessful()
        ->assertJsonStructure([
            '*' => [
                'date',
                'value',
                'income',
                'expense',
            ],
        ]);
});

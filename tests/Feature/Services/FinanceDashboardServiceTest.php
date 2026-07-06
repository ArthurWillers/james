<?php

use App\Enums\FinancialAccountType;
use App\Models\FinancialAccount;
use App\Models\FinancialTransaction;
use App\Services\FinanceDashboardService;
use Illuminate\Support\Carbon;

it('calculates KPIs correctly', function () {
    $account = FinancialAccount::factory()->create(['type' => FinancialAccountType::Checking]);

    FinancialTransaction::factory()->create([
        'financial_account_id' => $account->id,
        'type' => 'income',
        'amount' => 500,
        'date' => Carbon::now()->format('Y-m-d'),
        'is_posted' => true,
    ]);

    FinancialTransaction::factory()->create([
        'financial_account_id' => $account->id,
        'type' => 'expense',
        'amount' => 200,
        'date' => Carbon::now()->format('Y-m-d'),
        'is_posted' => true,
    ]);

    $service = new FinanceDashboardService;
    $kpis = $service->getKpiNumbers(false);

    expect($kpis['income'])->toEqual(500)
        ->and($kpis['expense'])->toEqual(200)
        ->and($kpis['currentBalance'])->toEqual(300)
        ->and($kpis['netBalance'])->toEqual(300); // Because account balance is also 300
});

it('calculates cash flow projections', function () {
    $account = FinancialAccount::factory()->create(['type' => FinancialAccountType::Checking]);

    // Add transaction to give initial balance
    FinancialTransaction::factory()->create([
        'financial_account_id' => $account->id,
        'type' => 'income',
        'amount' => 1000,
        'date' => Carbon::now()->format('Y-m-d'),
        'is_posted' => true,
    ]);

    $service = new FinanceDashboardService;
    $projections = $service->getCashFlowProjections(Carbon::now());

    expect($projections['currentMonth'])->toEqual(1000)
        ->and($projections['nextMonth'])->toEqual(1000);
});

it('returns account balances chart data', function () {
    $account = FinancialAccount::factory()->create(['name' => 'Main Account']);

    FinancialTransaction::factory()->create([
        'financial_account_id' => $account->id,
        'type' => 'income',
        'amount' => 500,
        'date' => Carbon::now()->format('Y-m-d'),
        'is_posted' => true,
    ]);

    $service = new FinanceDashboardService;
    $data = $service->getAccountBalancesChart();

    expect($data)->toHaveCount(1)
        ->and($data[0]['name'])->toEqual('Main Account')
        ->and($data[0]['value'])->toEqual(500);
});

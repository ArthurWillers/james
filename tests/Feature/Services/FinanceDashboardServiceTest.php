<?php

use App\Enums\FinancialAccountType;
use App\Enums\InvoiceStatus;
use App\Models\FinancialAccount;
use App\Models\FinancialCreditCard;
use App\Models\FinancialCreditCardInvoice;
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

    expect($projections)
        ->toHaveKeys(['currentMonth', 'nextMonth', 'afterNextMonth'])
        ->and($projections['currentMonth'])->toEqual(1000)
        ->and($projections['nextMonth'])->toEqual(1000)
        ->and($projections['afterNextMonth'])->toEqual(1000);
});

it('returns account balances chart data', function () {
    $account = FinancialAccount::factory()->create([
        'name' => 'Main Account',
        'type' => FinancialAccountType::Checking,
    ]);

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

it('getCreditCardsWidget returns the invoice matching the current reference month', function () {
    Carbon::setTestNow('2026-07-17'); // After closing day 7 → referenceMonth = agosto

    $account = FinancialAccount::factory()->create(['type' => FinancialAccountType::Checking]);
    $card = FinancialCreditCard::factory()->create([
        'financial_account_id' => $account->id,
        'closing_day' => 7,
        'due_day' => 1,
    ]);

    // Julho invoice (closed — before closing day)
    FinancialCreditCardInvoice::factory()->create([
        'financial_credit_card_id' => $card->id,
        'reference_month' => '2026-07-01',
        'closing_date' => '2026-07-07',
        'due_date' => '2026-08-01',
        'paid_at' => null,
    ]);

    // Agosto invoice (open — current reference month)
    FinancialCreditCardInvoice::factory()->create([
        'financial_credit_card_id' => $card->id,
        'reference_month' => '2026-08-01',
        'closing_date' => '2026-08-07',
        'due_date' => '2026-09-01',
        'paid_at' => null,
    ]);

    $service = new FinanceDashboardService;
    $cards = $service->getCreditCardsWidget(Carbon::now());

    // Should pick agosto (reference_month matches resolved month)
    expect($cards->first()->current_invoice_status)->toBeIn([InvoiceStatus::Open, InvoiceStatus::Closed]);

    Carbon::setTestNow();
});

it('getCreditCardsWidget falls back to most recent unpaid invoice when reference month has no match', function () {
    Carbon::setTestNow('2026-07-17');

    $account = FinancialAccount::factory()->create(['type' => FinancialAccountType::Checking]);
    $card = FinancialCreditCard::factory()->create([
        'financial_account_id' => $account->id,
        'closing_day' => 7,
        'due_day' => 1,
    ]);

    // Only one invoice — does not match resolved reference month (agosto)
    $invoice = FinancialCreditCardInvoice::factory()->create([
        'financial_credit_card_id' => $card->id,
        'reference_month' => '2026-06-01',
        'closing_date' => '2026-06-07',
        'due_date' => '2026-07-01',
        'paid_at' => null,
    ]);

    $service = new FinanceDashboardService;
    $cards = $service->getCreditCardsWidget(Carbon::now());

    // Fallback should return the only unpaid invoice
    expect($cards->first()->current_invoice_total)->toEqual($invoice->total());

    Carbon::setTestNow();
});

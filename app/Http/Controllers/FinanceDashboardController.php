<?php

namespace App\Http\Controllers;

use App\Services\FinanceDashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class FinanceDashboardController extends Controller
{
    public function __construct(private FinanceDashboardService $dashboardService)
    {
    }

    public function __invoke(Request $request)
    {
        $referenceDate = Carbon::today();

        $kpi = $this->dashboardService->getKpiNumbers($referenceDate);
        $projections = $this->dashboardService->getCashFlowProjections($referenceDate);
        $cardsWidget = $this->dashboardService->getCreditCardsWidget($referenceDate);
        $radar = $this->dashboardService->getJamesRadar($referenceDate);
        $topExpensesChart = $this->dashboardService->getExpensesByTagChart($referenceDate);
        $recentTransactions = $this->dashboardService->getRecentTransactions($referenceDate);

        $accountBalancesChart = $this->dashboardService->getAccountBalancesChart();

        return view('finance.dashboard', compact(
            'kpi',
            'projections',
            'cardsWidget',
            'radar',
            'topExpensesChart',
            'recentTransactions',
            'accountBalancesChart'
        ));
    }
}

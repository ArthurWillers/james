<?php

namespace App\Http\Controllers;

use App\Services\FinanceDashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class FinanceDashboardController extends Controller
{
    public function __construct(private FinanceDashboardService $dashboardService) {}

    public function __invoke(Request $request)
    {
        $referenceDate = Carbon::today();
        $includeInvestments = $request->boolean('include_investments', false);

        $kpi = $this->dashboardService->getKpiNumbers($includeInvestments);
        $projections = $this->dashboardService->getCashFlowProjections($referenceDate, $includeInvestments);
        $cardsWidget = $this->dashboardService->getCreditCardsWidget($referenceDate);
        $radar = $this->dashboardService->getJamesRadar($referenceDate);
        $topExpenseTags = $this->dashboardService->getTopExpenseTags($referenceDate);
        $recentTransactions = $this->dashboardService->getRecentTransactions();

        $accountBalancesChart = $this->dashboardService->getAccountBalancesChart(null, $includeInvestments);

        return view('finance.dashboard', compact(
            'kpi',
            'projections',
            'cardsWidget',
            'radar',
            'topExpenseTags',
            'recentTransactions',
            'accountBalancesChart',
            'includeInvestments'
        ));
    }
}

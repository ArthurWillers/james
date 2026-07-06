<?php

namespace App\Http\Controllers;

use App\Models\FinancialAccount;
use App\Models\FinancialCreditCardInvoice;
use App\Models\FinancialTransaction;
use App\Services\ReportsService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

class ReportsController extends Controller
{
    public function __construct(private ReportsService $reportsService) {}

    public function index(Request $request)
    {
        $period = $request->input('period', 'this_month');
        $interval = $request->input('interval', 'auto');
        $accountId = $request->input('account');
        
        $accountIds = null;
        if ($accountId) {
            if (str_starts_with($accountId, 'type:')) {
                $type = substr($accountId, 5);
                $accountIds = FinancialAccount::where('type', $type)->pluck('id')->toArray();
            } else {
                $accountIds = [$accountId];
            }
        }

        $now = Carbon::today();

        if ($period === 'custom' && $request->filled('startDate') && $request->filled('endDate')) {
            $startDate = Carbon::parse($request->input('startDate'))->startOfDay();
            $endDate = Carbon::parse($request->input('endDate'))->endOfDay();
        } elseif ($period === 'all_time') {
            $minDate = FinancialTransaction::min('date');
            $startDate = $minDate ? Carbon::parse($minDate)->startOfDay() : $now->copy()->subYears(5)->startOfDay();
            $maxInvoiceDate = FinancialCreditCardInvoice::max('due_date');
            $maxTransactionDate = FinancialTransaction::max('date');
            $maxDate = max($maxInvoiceDate, $maxTransactionDate, $now->format('Y-m-d'));
            $endDate = Carbon::parse($maxDate)->endOfDay();
        } elseif ($period === 'until_today') {
            $minDate = FinancialTransaction::min('date');
            $startDate = $minDate ? Carbon::parse($minDate)->startOfDay() : $now->copy()->subYears(5)->startOfDay();
            $endDate = $now->copy()->endOfDay();
        } elseif ($period === 'this_month') {
            $startDate = $now->copy()->startOfMonth();
            $endDate = $now->copy()->endOfMonth();
        } elseif ($period === 'last_month') {
            $startDate = $now->copy()->subMonth()->startOfMonth();
            $endDate = $now->copy()->subMonth()->endOfMonth();
        } elseif ($period === 'last_3m') {
            $startDate = $now->copy()->subMonths(2)->startOfMonth();
            $endDate = $now->copy()->endOfDay();
        } elseif ($period === 'last_6m') {
            $startDate = $now->copy()->subMonths(5)->startOfMonth();
            $endDate = $now->copy()->endOfMonth();
        } elseif ($period === 'this_year') {
            $startDate = $now->copy()->startOfYear();
            $endDate = $now->copy()->endOfYear();
        } elseif ($period === 'next_month') {
            $startDate = $now->copy()->addMonth()->startOfMonth();
            $endDate = $now->copy()->addMonth()->endOfMonth();
        } elseif ($period === 'next_6m') {
            $startDate = $now->copy()->startOfMonth();
            $endDate = $now->copy()->addMonths(5)->endOfMonth();
        } elseif ($period === 'next_12m') {
            $startDate = $now->copy()->startOfMonth();
            $endDate = $now->copy()->addMonths(11)->endOfMonth();
        } else {
            // Default to this month
            $startDate = $now->copy()->startOfMonth();
            $endDate = $now->copy()->endOfMonth();
        }

        $reportData = $this->reportsService->getAll($startDate, $endDate, $accountIds, $interval);

        $accounts = FinancialAccount::orderBy('name')->get();

        $isSingleDay = $startDate->format('Y-m-d') === $endDate->format('Y-m-d');

        $allTransactions = $reportData['transactions'];

        $realTransactions = $allTransactions->reject(fn ($t) => isset($t->is_virtual) && $t->is_virtual);
        $virtualTransactions = $allTransactions->filter(fn ($t) => isset($t->is_virtual) && $t->is_virtual);

        $page = request()->get('page', 1);
        $virtualPage = request()->get('virtual_page', 1);
        $perPage = 50;

        $paginatedTransactions = new LengthAwarePaginator(
            $realTransactions->forPage($page, $perPage),
            $realTransactions->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query(), 'pageName' => 'page']
        );

        $paginatedVirtual = new LengthAwarePaginator(
            $virtualTransactions->forPage($virtualPage, $perPage),
            $virtualTransactions->count(),
            $perPage,
            $virtualPage,
            ['path' => request()->url(), 'query' => request()->query(), 'pageName' => 'virtual_page']
        );

        $accountBalancesChart = [];
        if (empty($accountId) || str_starts_with($accountId, 'type:')) {
            $accountBalancesChart = app(\App\Services\FinanceDashboardService::class)->getAccountBalancesChart($accountIds, true);
        }

        return view('finance.reports', [
            'accounts' => $accounts,
            'sankey' => $reportData['sankey'],
            'evolution' => $reportData['evolution'],
            'netWorthEvolution' => $reportData['netWorthEvolution'],
            'expenses' => $reportData['tags']['expenses'],
            'incomes' => $reportData['tags']['incomes'],
            'allExpenses' => $reportData['tags']['allExpenses'],
            'allIncomes' => $reportData['tags']['allIncomes'],
            'netTags' => $reportData['tags']['netTags'],
            'transactions' => $paginatedTransactions,
            'virtualTransactions' => $paginatedVirtual,
            'period' => $period,
            'interval' => $interval,
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
            'accountId' => $accountId,
            'isSingleDay' => $isSingleDay,
            'accountBalancesChart' => $accountBalancesChart,
        ]);
    }
}

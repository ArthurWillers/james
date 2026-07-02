<?php

namespace App\Http\Controllers;

use App\Models\FinancialAccount;
use App\Models\FinancialTransaction;
use App\Services\ReportsService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReportsController extends Controller
{
    public function __construct(private ReportsService $reportsService)
    {
    }

    public function index(Request $request)
    {
        $period = $request->input('period', 'last_3m');
        $interval = $request->input('interval', 'auto');
        $accountId = $request->input('account');
        $accountIds = $accountId ? [$accountId] : null;

        $now = Carbon::today();
        
        if ($period === 'custom' && $request->filled('startDate') && $request->filled('endDate')) {
            $startDate = Carbon::parse($request->input('startDate'))->startOfDay();
            $endDate = Carbon::parse($request->input('endDate'))->endOfDay();
        } elseif ($period === 'all_time') {
            $minDate = FinancialTransaction::min('date');
            $startDate = $minDate ? Carbon::parse($minDate)->startOfDay() : $now->copy()->subYears(5)->startOfDay();
            $maxInvoiceDate = \App\Models\FinancialCreditCardInvoice::max('due_date');
            $maxTransactionDate = FinancialTransaction::max('date');
            $maxDate = max($maxInvoiceDate, $maxTransactionDate, $now->format('Y-m-d'));
            $endDate = Carbon::parse($maxDate)->endOfMonth();
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
            $endDate = $now->copy()->endOfMonth();
        } elseif ($period === 'last_6m') {
            $startDate = $now->copy()->subMonths(5)->startOfMonth();
            $endDate = $now->copy()->endOfMonth();
        } elseif ($period === 'this_year') {
            $startDate = $now->copy()->startOfYear();
            $endDate = $now->copy()->endOfYear();
        } elseif ($period === 'next_6m') {
            $startDate = $now->copy()->startOfMonth();
            $endDate = $now->copy()->addMonths(5)->endOfMonth();
        } elseif ($period === 'next_12m') {
            $startDate = $now->copy()->startOfMonth();
            $endDate = $now->copy()->addMonths(11)->endOfMonth();
        } else {
            // Default to last 3m
            $startDate = $now->copy()->subMonths(2)->startOfMonth();
            $endDate = $now->copy()->endOfMonth();
        }

        $reportData = $this->reportsService->getAll($startDate, $endDate, $accountIds, $interval);
        
        $accounts = FinancialAccount::orderBy('name')->get();
        
        $isSingleDay = $startDate->format('Y-m-d') === $endDate->format('Y-m-d');

        return view('finance.reports', [
            'accounts' => $accounts,
            'sankey' => $reportData['sankey'],
            'evolution' => $reportData['evolution'],
            'expenses' => $reportData['tags']['expenses'],
            'incomes' => $reportData['tags']['incomes'],
            'allExpenses' => $reportData['tags']['allExpenses'],
            'allIncomes' => $reportData['tags']['allIncomes'],
            'transactions' => $reportData['transactions'],
            'period' => $period,
            'interval' => $interval,
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
            'accountId' => $accountId,
            'isSingleDay' => $isSingleDay,
        ]);
    }
}

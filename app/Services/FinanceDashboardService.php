<?php

namespace App\Services;

use App\Models\FinancialAccount;
use App\Models\FinancialCreditCard;
use App\Models\FinancialCreditCardInvoice;
use App\Models\FinancialRecurrence;
use App\Models\FinancialTag;
use App\Models\FinancialTransaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class FinanceDashboardService
{
    private ?float $accountBalanceCache = null;

    private function getAccountBalance(): float
    {
        if ($this->accountBalanceCache === null) {
            $this->accountBalanceCache = FinancialAccount::withBalance()->get()->sum('balance');
        }
        
        return $this->accountBalanceCache;
    }
    public function getKpiNumbers(): array
    {
        $accountBalance = $this->getAccountBalance();

        $creditCardDebt = FinancialCreditCardInvoice::whereNull('paid_at')
            ->withTotalAmount()
            ->get()
            ->sum(fn ($invoice) => max(0, $invoice->total() - $invoice->amount_paid));

        $netBalance = $accountBalance - $creditCardDebt;

        $income = FinancialTransaction::posted()
            ->incomes()
            ->withoutTransfers()
            ->sum('amount');

        $expense = FinancialTransaction::posted()
            ->expenses()
            ->withoutTransfers()
            ->sum('amount');

        $currentBalance = $income - $expense;

        return [
            'netBalance' => $netBalance,
            'income' => $income,
            'expense' => $expense,
            'currentBalance' => $currentBalance,
        ];
    }

    public function getCashFlowProjections(Carbon $referenceDate): array
    {
        $startOfMonth = $referenceDate->copy()->startOfMonth();
        $endOfMonth = $referenceDate->copy()->endOfMonth();

        $nextMonthStart = $referenceDate->copy()->addMonth()->startOfMonth();
        $nextMonthEnd = $referenceDate->copy()->addMonth()->endOfMonth();

        $accountBalance = $this->getAccountBalance();

        // --- Mês Atual (Projeção) ---
        $pendingIncomeCurrent = FinancialTransaction::forPeriod($startOfMonth, $endOfMonth)
            ->pending()
            ->incomes()
            ->whereNull('financial_credit_card_invoice_id')
            ->withoutTransfers()
            ->sum('amount');

        $pendingExpenseCurrent = FinancialTransaction::forPeriod($startOfMonth, $endOfMonth)
            ->pending()
            ->expenses()
            ->whereNull('financial_credit_card_invoice_id')
            ->withoutTransfers()
            ->sum('amount');

        $openInvoicesCurrent = FinancialCreditCardInvoice::whereBetween('due_date', [$startOfMonth, $endOfMonth])
            ->whereNull('paid_at')
            ->withTotalAmount()
            ->get()
            ->sum(fn ($invoice) => max(0, $invoice->total() - $invoice->amount_paid));

        // Despesas Pendentes: Recorrências do mês atual (ainda não materializadas)
        // Assume-se que 'next_processing_date' indica o que falta processar
        $recurrencesCurrent = FinancialRecurrence::where('is_active', true)
            ->whereBetween('next_processing_date', [$referenceDate, $endOfMonth])
            ->get();

        $recurrencesIncomeCurrent = $recurrencesCurrent->where('type', 'income')->sum('amount');
        $recurrencesExpenseCurrent = $recurrencesCurrent->where('type', 'expense')->sum('amount');

        $projectionCurrentMonth = $accountBalance
            + $pendingIncomeCurrent + $recurrencesIncomeCurrent
            - $pendingExpenseCurrent - $openInvoicesCurrent - $recurrencesExpenseCurrent;

        $pendingIncomeNext = FinancialTransaction::forPeriod($nextMonthStart, $nextMonthEnd)
            ->pending()
            ->incomes()
            ->whereNull('financial_credit_card_invoice_id')
            ->withoutTransfers()
            ->sum('amount');

        $pendingExpenseNext = FinancialTransaction::forPeriod($nextMonthStart, $nextMonthEnd)
            ->pending()
            ->expenses()
            ->whereNull('financial_credit_card_invoice_id')
            ->withoutTransfers()
            ->sum('amount');

        $openInvoicesNext = FinancialCreditCardInvoice::whereBetween('due_date', [$nextMonthStart, $nextMonthEnd])
            ->whereNull('paid_at')
            ->withTotalAmount()
            ->get()
            ->sum(fn ($invoice) => max(0, $invoice->total() - $invoice->amount_paid));

        $recurrencesNext = FinancialRecurrence::where('is_active', true)
            ->where(function ($query) use ($nextMonthStart, $nextMonthEnd) {
                $query->whereBetween('next_processing_date', [$nextMonthStart, $nextMonthEnd])
                    ->orWhere(function ($q) use ($nextMonthStart, $nextMonthEnd) {
                        $q->where('next_processing_date', '<', $nextMonthStart)
                            ->where(function ($sq) use ($nextMonthEnd) {
                                $sq->whereNull('end_date')
                                    ->orWhere('end_date', '>=', $nextMonthEnd);
                            });
                    });
            })
            ->get();

        // Simplified recurrence calculation for next month (1 occurrence per active recurrence)
        // For a more robust calculation we'd need to simulate frequencies.
        // Given KISS principle, assuming 1x per month for monthly recurrences.
        $recurrencesIncomeNext = $recurrencesNext->where('type', 'income')->sum('amount');
        $recurrencesExpenseNext = $recurrencesNext->where('type', 'expense')->sum('amount');

        $projectionNextMonth = $projectionCurrentMonth
            + $pendingIncomeNext + $recurrencesIncomeNext
            - $pendingExpenseNext - $openInvoicesNext - $recurrencesExpenseNext;

        return [
            'currentMonth' => $projectionCurrentMonth,
            'nextMonth' => $projectionNextMonth,
        ];
    }

    public function getCreditCardsWidget(Carbon $referenceDate): Collection
    {
        return FinancialCreditCard::withUsedLimit()
            ->get()
            ->map(function ($card) use ($referenceDate) {
                $currentInvoice = FinancialCreditCardInvoice::resolveForDate($card, $referenceDate);
                
                // Load total_amount safely for the current invoice
                $loadedInvoice = FinancialCreditCardInvoice::withTotalAmount()->find($currentInvoice->id);
                
                $card->current_invoice_total = $loadedInvoice ? $loadedInvoice->total() : 0;
                $card->current_invoice_status = $loadedInvoice ? $loadedInvoice->status() : 'open';

                return $card;
            });
    }

    public function getJamesRadar(Carbon $referenceDate): Collection
    {
        $endDate = $referenceDate->copy()->addDays(15);

        $pendingTransactions = FinancialTransaction::pending()
            ->whereBetween('date', [$referenceDate, $endDate])
            ->whereNull('financial_credit_card_invoice_id')
            ->whereDoesntHave('tags', fn ($q) => $q->where('financial_tag_id', FinancialTag::TRANSFERENCIA_ID))
            ->get()
            ->map(fn ($t) => (object) [
                'type_label' => 'Transação Agendada',
                'title' => $t->description,
                'amount' => $t->amount,
                'type' => $t->type,
                'date' => $t->date,
                'icon' => 'heroicon-o-clock',
            ]);

        $recurrences = FinancialRecurrence::where('is_active', true)
            ->whereBetween('next_processing_date', [$referenceDate, $endDate])
            ->get()
            ->map(fn ($r) => (object) [
                'type_label' => 'Recorrência ('.($r->frequency === 'monthly' ? 'Mensal' : 'Anual').')',
                'title' => $r->title,
                'amount' => $r->amount,
                'type' => $r->type,
                'date' => $r->next_processing_date,
                'icon' => 'heroicon-o-arrow-path',
            ]);

        $openInvoices = FinancialCreditCardInvoice::with(['card'])
            ->withTotalAmount()
            ->whereBetween('due_date', [$referenceDate, $endDate])
            ->whereNull('paid_at')
            ->get()
            ->map(function ($inv) {
                return (object) [
                    'type_label' => 'Fatura de Cartão',
                    'title' => 'Fatura '.$inv->card->name,
                    'amount' => max(0, $inv->total() - $inv->amount_paid),
                    'type' => 'expense',
                    'date' => $inv->due_date,
                    'icon' => 'heroicon-o-credit-card',
                ];
            });

        return $pendingTransactions->concat($recurrences)->concat($openInvoices)->sortBy('date')->values();
    }

    public function getTop5Expenses(Carbon $referenceDate): Collection
    {
        $startOfMonth = $referenceDate->copy()->startOfMonth();
        $endOfMonth = $referenceDate->copy()->endOfMonth();

        $expenses = FinancialTransaction::forPeriod($startOfMonth, $endOfMonth)
            ->posted()
            ->expenses()
            ->withoutTransfers()
            ->with(['tags' => fn ($q) => $q->wherePivot('is_primary', true)])
            ->get();

        $grouped = $expenses->groupBy(function ($transaction) {
            $primaryTag = $transaction->tags->first();

            return $primaryTag ? $primaryTag->id : 0;
        });

        $topTags = $grouped->map(function ($transactions, $tagId) {
            $tag = $transactions->first()->tags->first();

            return (object) [
                'tag_name' => $tag ? $tag->name : 'Sem Categoria',
                'color_hex' => $tag ? $tag->color_hex : '#9ca3af',
                'icon' => $tag ? $tag->icon : 'heroicon-o-tag',
                'total' => $transactions->sum('amount'),
            ];
        })->sortByDesc('total')->take(5)->values();

        return $topTags;
    }

    public function getRecentTransactions(Carbon $referenceDate): Collection
    {
        return FinancialTransaction::with(['account', 'invoice', 'tags', 'recurrence'])
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get();
    }

    public function getNetWorthChartData(string $period): array
    {
        $today = Carbon::today();
        
        $startDate = match ($period) {
            '1m' => $today->copy()->subMonth(),
            '3m' => $today->copy()->subMonths(3),
            '6m' => $today->copy()->subMonths(6),
            '1y' => $today->copy()->subYear(),
            'all' => Carbon::parse(FinancialTransaction::min('date') ?? $today->copy()->subYears(5)),
            default => $today->copy()->subMonth(),
        };

        $groupBy = in_array($period, ['1y', 'all']) ? 'week' : 'day';

        // Get initial balance before start date
        $initialBalance = FinancialTransaction::where('is_posted', true)
            ->where('date', '<', $startDate)
            ->selectRaw("COALESCE(SUM(CASE WHEN type = 'income' THEN amount WHEN type = 'expense' THEN -amount ELSE 0 END), 0) as balance")
            ->value('balance') ?? 0;

        // Get flows during the period
        $flows = FinancialTransaction::where('is_posted', true)
            ->whereBetween('date', [$startDate, $today])
            ->withoutTransfers()
            ->selectRaw("
                date, 
                COALESCE(SUM(CASE WHEN type = 'income' THEN amount WHEN type = 'expense' THEN -amount ELSE 0 END), 0) as net_flow,
                COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) as income,
                COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) as expense
            ")
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $flowsByDate = $flows->mapWithKeys(function ($item) {
            $dateStr = is_string($item->date) ? substr($item->date, 0, 10) : $item->date->format('Y-m-d');
            return [$dateStr => [
                'net_flow' => (float) $item->net_flow,
                'income' => (float) $item->income,
                'expense' => (float) $item->expense,
            ]];
        });

        $chartData = [];
        $runningBalance = (float) $initialBalance;
        
        $currentDate = $startDate->copy();
        
        if ($groupBy === 'day') {
            while ($currentDate->lte($today)) {
                $dateStr = $currentDate->format('Y-m-d');
                $flowData = $flowsByDate->get($dateStr, ['net_flow' => 0, 'income' => 0, 'expense' => 0]);
                
                $runningBalance += $flowData['net_flow'];
                
                $chartData[] = [
                    'date' => $dateStr,
                    'value' => round($runningBalance, 2),
                    'income' => round($flowData['income'], 2),
                    'expense' => round($flowData['expense'], 2),
                ];
                
                $currentDate->addDay();
            }
        } else {
            // Group by week
            $currentDate = $startDate->copy()->startOfWeek();
            while ($currentDate->lte($today)) {
                $weekEnd = $currentDate->copy()->endOfWeek();
                if ($weekEnd->gt($today)) {
                    $weekEnd = $today->copy();
                }
                
                $weeklyFlow = 0;
                $weeklyIncome = 0;
                $weeklyExpense = 0;
                
                $loopDate = $currentDate->copy();
                while ($loopDate->lte($weekEnd)) {
                    $flowData = $flowsByDate->get($loopDate->format('Y-m-d'), ['net_flow' => 0, 'income' => 0, 'expense' => 0]);
                    $weeklyFlow += $flowData['net_flow'];
                    $weeklyIncome += $flowData['income'];
                    $weeklyExpense += $flowData['expense'];
                    $loopDate->addDay();
                }
                
                $runningBalance += $weeklyFlow;
                
                $chartData[] = [
                    'date' => $weekEnd->format('Y-m-d'),
                    'value' => round($runningBalance, 2),
                    'income' => round($weeklyIncome, 2),
                    'expense' => round($weeklyExpense, 2),
                ];
                
                $currentDate->addWeek();
            }
        }

        return $chartData;
    }
}

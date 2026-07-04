<?php

namespace App\Services;

use App\Models\FinancialAccount;
use App\Models\FinancialCreditCard;
use App\Models\FinancialCreditCardInvoice;
use App\Models\FinancialRecurrence;
use App\Models\FinancialTransaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class FinanceDashboardService
{
    private ?Collection $accountsCache = null;

    private ?Collection $activeRecurrencesCache = null;

    private ?Collection $creditCardsCache = null;

    private function getAccounts(): Collection
    {
        if ($this->accountsCache === null) {
            $this->accountsCache = FinancialAccount::withBalance()->get();
        }

        return $this->accountsCache;
    }

    private function getActiveRecurrences(): Collection
    {
        if ($this->activeRecurrencesCache === null) {
            $this->activeRecurrencesCache = FinancialRecurrence::active()
                ->with(['tags'])
                ->get()
                ->each(function ($r) {
                    if ($r->financial_account_id) {
                        $r->setRelation('financialAccount', $this->getAccounts()->firstWhere('id', $r->financial_account_id));
                    }
                    if ($r->financial_credit_card_id) {
                        $r->setRelation('financialCreditCard', $this->getCreditCards()->firstWhere('id', $r->financial_credit_card_id));
                    }
                });
        }

        return $this->activeRecurrencesCache;
    }

    private function getCreditCards(): Collection
    {
        if ($this->creditCardsCache === null) {
            $this->creditCardsCache = FinancialCreditCard::withUsedLimit()
                ->with(['invoices' => fn ($q) => $q->withTotalAmount()])
                ->get();
        }

        return $this->creditCardsCache;
    }

    private function getAccountBalance(): float
    {
        return (float) $this->getAccounts()->sum('balance');
    }

    public function getKpiNumbers(): array
    {
        $accountBalance = $this->getAccountBalance();

        $creditCardDebt = $this->getOpenInvoicesTotalForPeriod();

        $otherDebts = FinancialTransaction::pending()
            ->expenses()
            ->withoutTransfers()
            ->withoutInvoice()
            ->sum('amount');

        $netBalance = $accountBalance - $creditCardDebt - $otherDebts;

        $totals = FinancialTransaction::posted()
            ->withoutTransfers()
            ->toBase()
            ->selectRaw("
                COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) as income,
                COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) as expense
            ")
            ->first();

        $income = (float) $totals->income;
        $expense = (float) $totals->expense;
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
        $pendingCurrent = $this->getPendingTotalsForPeriod($startOfMonth, $endOfMonth);
        $openInvoicesCurrent = $this->getOpenInvoicesTotalForPeriod($startOfMonth, $endOfMonth);

        // Recorrências do mês atual (ainda não materializadas)
        $recurrencesCurrent = $this->getActiveRecurrences()
            ->filter(fn ($r) => $r->next_processing_date->between($referenceDate, $endOfMonth));

        $recurrencesIncomeCurrent = $recurrencesCurrent->where('type', 'income')->sum('amount');
        $recurrencesExpenseCurrent = $recurrencesCurrent->where('type', 'expense')->sum('amount');

        $projectionCurrentMonth = $accountBalance
            + (float) $pendingCurrent->income + $recurrencesIncomeCurrent
            - (float) $pendingCurrent->expense - $openInvoicesCurrent - $recurrencesExpenseCurrent;

        // --- Próximo Mês (Projeção) ---
        $pendingNext = $this->getPendingTotalsForPeriod($nextMonthStart, $nextMonthEnd);
        $openInvoicesNext = $this->getOpenInvoicesTotalForPeriod($nextMonthStart, $nextMonthEnd);

        // Recorrências do próximo mês (filtradas do cache)
        $recurrencesNext = $this->getActiveRecurrences()
            ->filter(function ($r) use ($nextMonthStart, $nextMonthEnd) {
                return $r->next_processing_date->between($nextMonthStart, $nextMonthEnd)
                    || ($r->next_processing_date->lt($nextMonthStart)
                        && ($r->end_date === null || $r->end_date->gte($nextMonthEnd)));
            });

        $recurrencesIncomeNext = $recurrencesNext->where('type', 'income')->sum('amount');
        $recurrencesExpenseNext = $recurrencesNext->where('type', 'expense')->sum('amount');

        $projectionNextMonth = $projectionCurrentMonth
            + (float) $pendingNext->income + $recurrencesIncomeNext
            - (float) $pendingNext->expense - $openInvoicesNext - $recurrencesExpenseNext;

        return [
            'currentMonth' => $projectionCurrentMonth,
            'nextMonth' => $projectionNextMonth,
        ];
    }

    private function getPendingTotalsForPeriod(Carbon $start, Carbon $end): object
    {
        return FinancialTransaction::forPeriod($start, $end)
            ->pending()
            ->withoutInvoice()
            ->withoutTransfers()
            ->toBase()
            ->selectRaw("
                COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) as income,
                COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) as expense
            ")
            ->first();
    }

    private function getOpenInvoicesTotalForPeriod(?Carbon $start = null, ?Carbon $end = null): float
    {
        $query = FinancialCreditCardInvoice::unpaid()->withTotalAmount();

        if ($start && $end) {
            $query->dueBetween($start, $end);
        }

        return (float) $query->get()->sum(fn ($invoice) => max(0, $invoice->total() - $invoice->amount_paid));
    }

    public function getAccountBalancesChart(): array
    {
        return $this->getAccounts()
            ->filter(fn ($account) => $account->balance > 0)
            ->map(fn ($account) => [
                'value' => round($account->balance, 2),
                'name' => $account->name,
            ])
            ->values()
            ->toArray();
    }

    public function getCreditCardsWidget(Carbon $referenceDate): Collection
    {
        return $this->getCreditCards()
            ->map(function ($card) use ($referenceDate) {
                $referenceMonth = $this->resolveReferenceMonth($card, $referenceDate);

                $currentInvoice = $card->invoices
                    ->first(fn ($inv) => $inv->reference_month?->startOfMonth()->eq($referenceMonth));

                $card->current_invoice_total = $currentInvoice ? $currentInvoice->total() : 0;
                $card->current_invoice_status = $currentInvoice ? $currentInvoice->status() : 'open';

                return $card;
            });
    }

    /**
     * Resolve the reference month for a credit card invoice based on the purchase date.
     * Pure calculation — no database queries.
     */
    private function resolveReferenceMonth(FinancialCreditCard $card, Carbon $date): Carbon
    {
        $candidateMonth = $date->copy()->startOfMonth();
        $closingDate = $candidateMonth->copy()->day((int) min($card->closing_day, $candidateMonth->daysInMonth));

        if ($date->copy()->startOfDay()->lte($closingDate)) {
            return $candidateMonth;
        }

        return $candidateMonth->addMonth()->startOfMonth();
    }

    public function getJamesRadar(Carbon $referenceDate): Collection
    {
        $endDate = $referenceDate->copy()->addMonthNoOverflow();

        $pendingTransactions = FinancialTransaction::pending()
            ->forPeriod($referenceDate, $endDate)
            ->withoutInvoice()
            ->withoutTransfers()
            ->with(['tags'])
            ->get()
            ->each(function ($t) {
                $t->setRelation('account', $t->financial_account_id ? $this->getAccounts()->firstWhere('id', $t->financial_account_id) : null);
            });

        $recurrences = $this->getActiveRecurrences()
            ->filter(fn ($r) => $r->next_processing_date->between($referenceDate, $endDate))
            ->map(function ($r) {
                $t = new FinancialTransaction([
                    'description' => $r->title,
                    'amount' => $r->amount,
                    'type' => $r->type,
                    'date' => $r->next_processing_date,
                    'is_posted' => false,
                ]);
                $t->is_recurrence = true;
                $t->setRelation('tags', $r->tags);

                if ($r->financial_credit_card_id) {
                    $fakeInvoice = new FinancialCreditCardInvoice;
                    $fakeInvoice->setRelation('creditCard', $r->relationLoaded('financialCreditCard') ? $r->financialCreditCard : null);
                    $t->setRelation('invoice', $fakeInvoice);
                } else {
                    $t->setRelation('account', $r->relationLoaded('financialAccount') ? $r->financialAccount : null);
                }

                return $t;
            });

        $openInvoices = FinancialCreditCardInvoice::withTotalAmount()
            ->dueBetween($referenceDate, $endDate)
            ->unpaid()
            ->get()
            ->map(function ($inv) {
                $inv->setRelation('creditCard', $this->getCreditCards()->firstWhere('id', $inv->financial_credit_card_id));
                $t = new FinancialTransaction([
                    'description' => 'Fatura '.($inv->creditCard?->name ?? 'Cartão'),
                    'amount' => max(0, $inv->total() - $inv->amount_paid),
                    'type' => 'expense',
                    'date' => $inv->due_date,
                    'is_posted' => false,
                ]);
                $t->is_invoice = true;
                $t->setRelation('tags', collect());
                $t->setRelation('invoice', $inv);

                return $t;
            });

        return $pendingTransactions->concat($recurrences)->concat($openInvoices)->sortBy('date')->values();
    }

    public function getTopExpenseTags(Carbon $referenceDate): array
    {
        $startDate = $referenceDate->copy()->subDays(30);
        $endDate = $referenceDate->copy();

        $expenses = FinancialTransaction::forPeriod($startDate, $endDate)
            ->posted()
            ->expenses()
            ->withoutTransfers()
            ->with(['tags' => fn ($q) => $q->wherePivot('is_primary', true)])
            ->get();

        $grouped = $expenses->groupBy(function ($transaction) {
            $primaryTag = $transaction->tags->first();

            return $primaryTag ? $primaryTag->id : 0;
        });

        return $grouped->map(function ($transactions) {
            $tag = $transactions->first()->tags->first();

            return [
                'name' => $tag ? $tag->name : 'Sem Categoria',
                'value' => (float) $transactions->sum('amount'),
                'color' => $tag ? $tag->color_hex : '#9ca3af',
                'icon' => $tag ? $tag->icon : 'heroicon-o-tag',
            ];
        })->sortByDesc('value')->take(5)->values()->toArray();
    }

    public function getRecentTransactions(): Collection
    {
        $transactions = FinancialTransaction::with(['invoice', 'tags', 'recurrence'])
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get();

        $transactions->each(function ($t) {
            $t->setRelation('account', $t->financial_account_id ? $this->getAccounts()->firstWhere('id', $t->financial_account_id) : null);

            if ($t->relationLoaded('invoice') && $t->invoice) {
                $t->invoice->setRelation('creditCard', $t->invoice->financial_credit_card_id ? $this->getCreditCards()->firstWhere('id', $t->invoice->financial_credit_card_id) : null);
            }
        });

        return $transactions;
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
        $initialBalance = FinancialTransaction::posted()
            ->where('date', '<', $startDate)
            ->selectRaw("COALESCE(SUM(CASE WHEN type = 'income' THEN amount WHEN type = 'expense' THEN -amount ELSE 0 END), 0) as balance")
            ->value('balance') ?? 0;

        // Get flows during the period
        $flows = FinancialTransaction::posted()
            ->forPeriod($startDate, $today)
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

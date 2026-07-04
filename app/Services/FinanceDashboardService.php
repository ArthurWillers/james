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
            $this->activeRecurrencesCache = FinancialRecurrence::active()->get();
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

        $creditCardDebt = FinancialCreditCardInvoice::unpaid()
            ->withTotalAmount()
            ->get()
            ->sum(fn ($invoice) => max(0, $invoice->total() - $invoice->amount_paid));

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
        $pendingCurrent = FinancialTransaction::forPeriod($startOfMonth, $endOfMonth)
            ->pending()
            ->withoutInvoice()
            ->withoutTransfers()
            ->toBase()
            ->selectRaw("
                COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) as income,
                COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) as expense
            ")
            ->first();

        $openInvoicesCurrent = FinancialCreditCardInvoice::dueBetween($startOfMonth, $endOfMonth)
            ->unpaid()
            ->withTotalAmount()
            ->get()
            ->sum(fn ($invoice) => max(0, $invoice->total() - $invoice->amount_paid));

        // Recorrências do mês atual (ainda não materializadas)
        $recurrencesCurrent = $this->getActiveRecurrences()
            ->filter(fn ($r) => $r->next_processing_date->between($referenceDate, $endOfMonth));

        $recurrencesIncomeCurrent = $recurrencesCurrent->where('type', 'income')->sum('amount');
        $recurrencesExpenseCurrent = $recurrencesCurrent->where('type', 'expense')->sum('amount');

        $projectionCurrentMonth = $accountBalance
            + (float) $pendingCurrent->income + $recurrencesIncomeCurrent
            - (float) $pendingCurrent->expense - $openInvoicesCurrent - $recurrencesExpenseCurrent;

        // --- Próximo Mês (Projeção) ---
        $pendingNext = FinancialTransaction::forPeriod($nextMonthStart, $nextMonthEnd)
            ->pending()
            ->withoutInvoice()
            ->withoutTransfers()
            ->toBase()
            ->selectRaw("
                COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) as income,
                COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) as expense
            ")
            ->first();

        $openInvoicesNext = FinancialCreditCardInvoice::dueBetween($nextMonthStart, $nextMonthEnd)
            ->unpaid()
            ->withTotalAmount()
            ->get()
            ->sum(fn ($invoice) => max(0, $invoice->total() - $invoice->amount_paid));

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
            ->get()
            ->map(fn ($t) => (object) [
                'type_label' => 'Transação Agendada',
                'title' => $t->description,
                'amount' => $t->amount,
                'type' => $t->type,
                'date' => $t->date,
                'icon' => 'heroicon-o-clock',
            ]);

        $recurrences = $this->getActiveRecurrences()
            ->filter(fn ($r) => $r->next_processing_date->between($referenceDate, $endDate))
            ->map(fn ($r) => (object) [
                'type_label' => 'Recorrência ('.($r->frequency === 'monthly' ? 'Mensal' : 'Anual').')',
                'title' => $r->title,
                'amount' => $r->amount,
                'type' => $r->type,
                'date' => $r->next_processing_date,
                'icon' => 'heroicon-o-arrow-path',
            ]);

        $creditCardsById = $this->getCreditCards()->keyBy('id');

        $openInvoices = FinancialCreditCardInvoice::withTotalAmount()
            ->dueBetween($referenceDate, $endDate)
            ->unpaid()
            ->get()
            ->map(function ($inv) use ($creditCardsById) {
                $cardName = $creditCardsById->get($inv->financial_credit_card_id)?->name ?? 'Cartão';

                return (object) [
                    'type_label' => 'Fatura de Cartão',
                    'title' => 'Fatura '.$cardName,
                    'amount' => max(0, $inv->total() - $inv->amount_paid),
                    'type' => 'expense',
                    'date' => $inv->due_date,
                    'icon' => 'heroicon-o-credit-card',
                ];
            });

        return $pendingTransactions->concat($recurrences)->concat($openInvoices)->sortBy('date')->values();
    }

    public function getExpensesByTagChart(Carbon $referenceDate): array
    {
        $startDate = $referenceDate->copy()->subDays(30);
        $endDate = $referenceDate->copy();

        $transactions = FinancialTransaction::forPeriod($startDate, $endDate)
            ->posted()
            ->withoutTransfers()
            ->with(['tags' => fn ($q) => $q->wherePivot('is_primary', true)])
            ->get();

        $grouped = $transactions->groupBy(function ($transaction) {
            $primaryTag = $transaction->tags->first();

            return $primaryTag ? $primaryTag->id : 0;
        });

        $tagsData = $grouped->map(function ($groupTransactions) {
            $tag = $groupTransactions->first()->tags->first();

            $netBalance = $groupTransactions->reduce(function ($carry, $transaction) {
                return $transaction->type === 'income' 
                    ? $carry + $transaction->amount 
                    : $carry - $transaction->amount;
            }, 0.0);

            // Ignora a tag se o saldo final for positivo ou zerado (receitas maiores ou iguais às despesas)
            if ($netBalance >= 0) {
                return null;
            }

            return [
                'name' => $tag ? $tag->name : 'Sem Categoria',
                'value' => (float) round(abs($netBalance), 2),
                'itemStyle' => [
                    'color' => $tag ? $tag->color_hex : '#9ca3af',
                ],
            ];
        })->filter()->sortByDesc('value')->values();

        $top10 = $tagsData->take(10);
        $others = $tagsData->slice(10);

        if ($others->isNotEmpty()) {
            $top10->push([
                'name' => 'Outros',
                'value' => (float) round($others->sum('value'), 2),
                'itemStyle' => [
                    'color' => '#d1d5db',
                ],
            ]);
        }

        return [
            'data' => $top10->toArray(),
            'total' => (float) round($tagsData->sum('value'), 2),
        ];
    }

    public function getRecentTransactions(): Collection
    {
        return FinancialTransaction::with(['account', 'invoice.creditCard', 'tags', 'recurrence'])
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

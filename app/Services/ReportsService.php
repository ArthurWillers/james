<?php

namespace App\Services;

use App\Models\FinancialCreditCardInvoice;
use App\Models\FinancialRecurrence;
use App\Models\FinancialTag;
use App\Models\FinancialTransaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ReportsService
{
    /**
     * Loads all report data in a single pass, fetching unified transactions only once.
     *
     * @return array{sankey: array, evolution: array, tags: array, transactions: Collection}
     */
    public function getAll(Carbon $startDate, Carbon $endDate, ?array $accountIds = null, string $interval = 'auto'): array
    {
        $transactions = $this->getUnifiedTransactions($startDate, $endDate, $accountIds);
        $flattenedForTags = $this->flattenTransactionsForTags($transactions);

        return [
            'sankey' => $this->buildSankeyData($flattenedForTags, $startDate, $accountIds),
            'evolution' => $this->buildEvolutionData($transactions, $startDate, $endDate, $accountIds, $interval),
            'tags' => $this->buildTagsData($flattenedForTags),
            'transactions' => $transactions,
        ];
    }

    /**
     * Gets a unified list of real and virtual transactions for the given period.
     */
    private function getUnifiedTransactions(Carbon $startDate, Carbon $endDate, ?array $accountIds = null): Collection
    {
        // 1. Real Transactions (includes future credit card installments since they are materialized)
        $query = FinancialTransaction::with(['tags', 'items.tags', 'invoice.creditCard', 'account'])
            ->whereBetween('date', [$startDate, $endDate])
            ->withoutTransfers();

        if (! empty($accountIds)) {
            $query->where(function ($q) use ($accountIds) {
                $q->whereIn('financial_account_id', $accountIds)
                    ->orWhereHas('invoice.creditCard', function ($q2) use ($accountIds) {
                        $q2->whereIn('financial_account_id', $accountIds);
                    });
            });
        }

        $realTransactions = $query->get()->map(function ($t) {
            $t->is_virtual = false;

            return $t;
        });

        // 2. Virtual Transactions from Recurrences
        $recurrenceQuery = FinancialRecurrence::with(['tags', 'financialAccount', 'financialCreditCard'])
            ->where('is_active', true)
            ->where(function ($q) use ($startDate) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', $startDate);
            });

        if (! empty($accountIds)) {
            $recurrenceQuery->where(function ($q) use ($accountIds) {
                $q->whereIn('financial_account_id', $accountIds)
                    ->orWhereHas('financialCreditCard', function ($q2) use ($accountIds) {
                        $q2->whereIn('financial_account_id', $accountIds);
                    });
            });
        }

        $recurrences = $recurrenceQuery->get();
        $virtualTransactions = collect();

        foreach ($recurrences as $recurrence) {
            $currentDate = $recurrence->next_processing_date ? $recurrence->next_processing_date->copy() : $recurrence->start_date->copy();

            // Fast forward to startDate if needed
            while ($currentDate->lt($startDate)) {
                $currentDate = $this->addFrequency($currentDate, $recurrence->frequency);
            }

            while ($currentDate->between($startDate, $endDate)) {
                $t = new FinancialTransaction([
                    'type' => $recurrence->type,
                    'amount' => $recurrence->amount,
                    'date' => $currentDate->copy(),
                    'description' => $recurrence->title,
                    'is_posted' => false,
                ]);

                $t->id = 'v_'.$recurrence->id.'_'.$currentDate->format('Ymd');
                $t->is_virtual = true;

                // Mock relations
                if ($recurrence->relationLoaded('tags')) {
                    $t->setRelation('tags', $recurrence->tags);
                }
                if ($recurrence->relationLoaded('financialAccount') && $recurrence->financialAccount) {
                    $t->setRelation('account', $recurrence->financialAccount);
                }
                if ($recurrence->relationLoaded('financialCreditCard') && $recurrence->financialCreditCard) {
                    // Create a fake invoice so the transaction-table component can read the credit card name
                    $fakeInvoice = new FinancialCreditCardInvoice;
                    $fakeInvoice->setRelation('creditCard', $recurrence->financialCreditCard);
                    $t->setRelation('invoice', $fakeInvoice);
                }

                $virtualTransactions->push($t);

                $currentDate = $this->addFrequency($currentDate, $recurrence->frequency);
            }
        }

        return $realTransactions->concat($virtualTransactions)->sortBy('date')->values();
    }

    private function flattenTransactionsForTags(Collection $transactions): Collection
    {
        $flattened = collect();

        foreach ($transactions as $t) {
            if ($t->relationLoaded('items') && $t->items->isNotEmpty()) {
                $itemsSum = 0;
                foreach ($t->items as $item) {
                    $itemAmount = $item->unit_price * $item->quantity;
                    $itemsSum += $itemAmount;
                    
                    // Create a fake entry
                    $entry = new \stdClass();
                    $entry->type = $t->type;
                    $entry->amount = $itemAmount;
                    $entry->tags = $item->tags;
                    
                    $flattened->push($entry);
                }
                
                $remainingAmount = $t->amount - $itemsSum;
                if ($remainingAmount > 0.01) {
                    $entry = new \stdClass();
                    $entry->type = $t->type;
                    $entry->amount = $remainingAmount;
                    $entry->tags = $t->tags;
                    $flattened->push($entry);
                }
            } else {
                $entry = new \stdClass();
                $entry->type = $t->type;
                $entry->amount = $t->amount;
                $entry->tags = $t->tags;
                $flattened->push($entry);
            }
        }
        
        return $flattened;
    }

    private function addFrequency(Carbon $date, string $frequency): Carbon
    {
        return match ($frequency) {
            'weekly' => $date->copy()->addWeek(),
            'monthly' => $date->copy()->addMonth(),
            'yearly' => $date->copy()->addYear(),
            default => $date->copy()->addMonth(),
        };
    }

    private function buildSankeyData(Collection $transactions, Carbon $startDate, ?array $accountIds = null): array
    {
        $nodes = collect();
        $links = collect();

        $incomes = $transactions->where('type', 'income');
        $expenses = $transactions->where('type', 'expense');

        $totalIncome = $incomes->sum('amount');
        $totalExpense = $expenses->sum('amount');

        // Central Node
        $nodes->push(['name' => 'Fluxo de Caixa', 'itemStyle' => ['color' => '#3b82f6']]);

        $getPrimaryTag = function ($t): ?FinancialTag {
            return $t->tags->where('pivot.is_primary', true)->first() ?? $t->tags->first();
        };

        // Income Nodes — group by primary tag name
        $incomeByTag = $incomes->groupBy(fn ($t) => optional($getPrimaryTag($t))->name ?? 'Sem Categoria');
        foreach ($incomeByTag as $tagName => $items) {
            $sum = $items->sum('amount');
            if ($sum > 0) {
                $tag = $getPrimaryTag($items->first());
                $nodeName = $tagName.' (R)';
                $nodes->push([
                    'name' => $nodeName,
                    'itemStyle' => ['color' => $tag?->color_hex ?? '#9ca3af'],
                ]);
                $links->push([
                    'source' => $nodeName,
                    'target' => 'Fluxo de Caixa',
                    'value' => round($sum, 2),
                ]);
            }
        }

        // Expense Nodes — group by primary tag name
        $expenseByTag = $expenses->groupBy(fn ($t) => optional($getPrimaryTag($t))->name ?? 'Sem Categoria');
        foreach ($expenseByTag as $tagName => $items) {
            $sum = $items->sum('amount');
            if ($sum > 0) {
                $tag = $getPrimaryTag($items->first());
                $nodes->push([
                    'name' => $tagName,
                    'itemStyle' => ['color' => $tag?->color_hex ?? '#9ca3af'],
                ]);
                $links->push([
                    'source' => 'Fluxo de Caixa',
                    'target' => $tagName,
                    'value' => round($sum, 2),
                ]);
            }
        }

        // Initial Balance (Saldo Anterior)
        $initialBalance = $this->getInitialBalance($startDate, $accountIds);
        if ($initialBalance > 0) {
            $nodes->push(['name' => 'Saldo Anterior', 'itemStyle' => ['color' => '#64748b']]);
            $links->push([
                'source' => 'Saldo Anterior',
                'target' => 'Fluxo de Caixa',
                'value' => round($initialBalance, 2),
            ]);
            $totalIncome += $initialBalance;
        }

        // Surplus / Deficit
        if ($totalIncome > $totalExpense) {
            $surplus = $totalIncome - $totalExpense;
            $nodes->push(['name' => 'Saldo', 'itemStyle' => ['color' => '#10b981']]);
            $links->push([
                'source' => 'Fluxo de Caixa',
                'target' => 'Saldo',
                'value' => round($surplus, 2),
            ]);
        } elseif ($totalExpense > $totalIncome) {
            $deficit = $totalExpense - $totalIncome;
            $nodes->push(['name' => 'Uso de Reservas (Déficit)', 'itemStyle' => ['color' => '#ef4444']]);
            $links->push([
                'source' => 'Uso de Reservas (Déficit)',
                'target' => 'Fluxo de Caixa',
                'value' => round($deficit, 2),
            ]);
        }

        $nodes = $nodes->unique('name')->values();

        return [
            'nodes' => $nodes->toArray(),
            'links' => $links->toArray(),
        ];
    }

    private function buildEvolutionData(Collection $transactions, Carbon $startDate, Carbon $endDate, ?array $accountIds, string $interval): array
    {
        $initialBalance = $this->getInitialBalance($startDate, $accountIds);

        $diffInDays = $startDate->diffInDays($endDate);

        if ($interval === 'auto') {
            if ($diffInDays <= 95) {
                $interval = 'daily';
            } elseif ($diffInDays <= 180) {
                $interval = 'weekly';
            } elseif ($diffInDays <= 730) {
                $interval = 'monthly';
            } else {
                $interval = 'yearly';
            }
        }

        $periods = [];
        $currentDate = $startDate->copy();

        if ($interval === 'daily') {
            while ($currentDate->lte($endDate)) {
                $periods[$currentDate->format('Y-m-d')] = ['income' => 0, 'expense' => 0];
                $currentDate->addDay();
            }
        } elseif ($interval === 'weekly') {
            $currentDate->startOfWeek();
            $endWeek = $endDate->copy()->startOfWeek();
            while ($currentDate->lte($endWeek)) {
                $periods[$currentDate->format('Y-m-d')] = ['income' => 0, 'expense' => 0];
                $currentDate->addWeek();
            }
        } elseif ($interval === 'yearly') {
            $currentDate->startOfYear();
            $endYear = $endDate->copy()->startOfYear();
            while ($currentDate->lte($endYear)) {
                $periods[$currentDate->format('Y')] = ['income' => 0, 'expense' => 0];
                $currentDate->addYear();
            }
        } else { // monthly
            $currentDate->startOfMonth();
            $endMonth = $endDate->copy()->startOfMonth();
            while ($currentDate->lte($endMonth)) {
                $periods[$currentDate->format('Y-m')] = ['income' => 0, 'expense' => 0];
                $currentDate->addMonth();
            }
        }

        $transferTagId = FinancialTag::TRANSFERENCIA_ID;
        $cashFlows = \DB::table('financial_transactions')
            ->leftJoin('financial_credit_card_invoices', 'financial_transactions.financial_credit_card_invoice_id', '=', 'financial_credit_card_invoices.id')
            ->whereNull('financial_transactions.deleted_at')
            ->whereNotIn('financial_transactions.id', function ($sub) use ($transferTagId) {
                $sub->select('financial_taggable_id')
                    ->from('financial_taggables')
                    ->where('financial_taggable_type', FinancialTransaction::class)
                    ->where('financial_tag_id', $transferTagId);
            });

        if (! empty($accountIds)) {
            $cashFlows->where(function ($sub) use ($accountIds) {
                $sub->whereIn('financial_transactions.financial_account_id', $accountIds)
                    ->orWhereExists(function ($ex) use ($accountIds) {
                        $ex->select(\DB::raw(1))
                            ->from('financial_credit_cards')
                            ->whereColumn('financial_credit_cards.id', 'financial_credit_card_invoices.financial_credit_card_id')
                            ->whereIn('financial_credit_cards.financial_account_id', $accountIds);
                    });
            });
        }

        $cashFlows = $cashFlows->whereRaw('COALESCE(financial_credit_card_invoices.due_date, financial_transactions.date) BETWEEN ? AND ?', [$startDate, $endDate])
            ->selectRaw('COALESCE(financial_credit_card_invoices.due_date, financial_transactions.date) as effective_date')
            ->selectRaw("SUM(CASE WHEN financial_transactions.type = 'income' THEN amount ELSE 0 END) as income")
            ->selectRaw("SUM(CASE WHEN financial_transactions.type = 'expense' THEN amount ELSE 0 END) as expense")
            ->groupBy('effective_date')
            ->get();

        $virtuals = $transactions->where('is_virtual', true);

        // Populate amounts
        foreach ($cashFlows as $cf) {
            $date = Carbon::parse($cf->effective_date);
            $key = $this->getPeriodKey($date, $interval);
            if (isset($periods[$key])) {
                $periods[$key]['income'] += (float) $cf->income;
                $periods[$key]['expense'] += (float) $cf->expense;
            }
        }

        foreach ($virtuals as $t) {
            $date = Carbon::parse($t->date);
            $key = $this->getPeriodKey($date, $interval);
            if (isset($periods[$key])) {
                if ($t->type === 'income') {
                    $periods[$key]['income'] += $t->amount;
                } else {
                    $periods[$key]['expense'] += $t->amount;
                }
            }
        }

        $chartData = [];
        $runningBalance = (float) $initialBalance;

        foreach ($periods as $key => $data) {
            $net = $data['income'] - $data['expense'];
            $runningBalance += $net;

            $chartData[] = [
                'date' => $key,
                'value' => round($runningBalance, 2),
                'income' => round($data['income'], 2),
                'expense' => round($data['expense'], 2),
            ];
        }

        return $chartData;
    }

    private function buildTagsData(Collection $transactions): array
    {
        $primaryExpenses = [];
        $primaryIncomes = [];
        $primaryTotalExp = 0;
        $primaryTotalInc = 0;

        $allExpenses = [];
        $allIncomes = [];

        foreach ($transactions as $t) {
            $amount = $t->amount;
            $tags = $t->tags->isEmpty() ? collect([(object) ['id' => 0, 'name' => 'Sem Categoria', 'color_hex' => '#cbd5e1', 'icon' => 'heroicon-o-tag', 'pivot' => (object) ['is_primary' => true]]]) : $t->tags;

            $primaryTag = $tags->first(fn ($tg) => ! empty($tg->pivot) && $tg->pivot->is_primary) ?? $tags->first();
            $isExpense = $t->type === 'expense';

            // primary tags logic
            $targetPrimary = &$primaryExpenses;
            if (! $isExpense) {
                $targetPrimary = &$primaryIncomes;
            }

            if (! isset($targetPrimary[$primaryTag->id])) {
                $targetPrimary[$primaryTag->id] = [
                    'id' => $primaryTag->id,
                    'name' => $primaryTag->name,
                    'color' => $primaryTag->color_hex ?? '#cbd5e1',
                    'icon' => $primaryTag->icon ?? 'heroicon-o-tag',
                    'value' => 0,
                ];
            }
            $targetPrimary[$primaryTag->id]['value'] += $amount;

            if ($isExpense) {
                $primaryTotalExp += $amount;
            } else {
                $primaryTotalInc += $amount;
            }

            // all tags logic
            foreach ($tags as $tag) {
                $id = $tag->id;

                $targetAll = &$allExpenses;
                if (! $isExpense) {
                    $targetAll = &$allIncomes;
                }

                if (! isset($targetAll[$id])) {
                    $targetAll[$id] = [
                        'id' => $id,
                        'name' => $tag->name,
                        'color' => $tag->color_hex ?? '#cbd5e1',
                        'icon' => $tag->icon ?? 'heroicon-o-tag',
                        'value' => 0,
                    ];
                }

                $targetAll[$id]['value'] += $amount;
            }
        }

        $primaryExpenses = collect(array_values($primaryExpenses))->sortByDesc('value')->map(function ($item) use ($primaryTotalExp) {
            $item['percentage'] = $primaryTotalExp > 0 ? round(($item['value'] / $primaryTotalExp) * 100, 1) : 0;

            return $item;
        })->values()->toArray();

        $primaryIncomes = collect(array_values($primaryIncomes))->sortByDesc('value')->map(function ($item) use ($primaryTotalInc) {
            $item['percentage'] = $primaryTotalInc > 0 ? round(($item['value'] / $primaryTotalInc) * 100, 1) : 0;

            return $item;
        })->values()->toArray();

        // Calculate Net Tags (Before destroying the ID keys of allExpenses and allIncomes)
        $netTags = [];
        $allTagIds = array_unique(array_merge(array_keys($allExpenses), array_keys($allIncomes)));

        foreach ($allTagIds as $id) {
            $incomeItem = $allIncomes[$id] ?? null;
            $expenseItem = $allExpenses[$id] ?? null;

            $incomeVal = $incomeItem ? $incomeItem['value'] : 0;
            $expenseVal = $expenseItem ? $expenseItem['value'] : 0;

            $baseItem = $incomeItem ?? $expenseItem;

            $netTags[] = [
                'id' => $id,
                'name' => $baseItem['name'],
                'color' => $baseItem['color'],
                'icon' => $baseItem['icon'],
                'income' => $incomeVal,
                'expense' => $expenseVal,
                'value' => $incomeVal - $expenseVal,
            ];
        }

        // Sort by value (highest profit first, highest loss last)
        $netTags = collect($netTags)->sortByDesc('value')->values()->toArray();

        $allExpenses = collect(array_values($allExpenses))->sortByDesc('value')->values()->toArray();
        $allIncomes = collect(array_values($allIncomes))->sortByDesc('value')->values()->toArray();

        return [
            'expenses' => $primaryExpenses,
            'incomes' => $primaryIncomes,
            'allExpenses' => $allExpenses,
            'allIncomes' => $allIncomes,
            'netTags' => $netTags,
            'totalExpense' => $primaryTotalExp,
            'totalIncome' => $primaryTotalInc,
        ];
    }

    private function getPeriodKey(Carbon $date, string $interval): string
    {
        return match ($interval) {
            'daily' => $date->format('Y-m-d'),
            'weekly' => $date->copy()->startOfWeek()->format('Y-m-d'),
            'yearly' => $date->format('Y'),
            default => $date->format('Y-m'),
        };
    }

    private function getInitialBalance(Carbon $startDate, ?array $accountIds = null): float
    {
        $transferTagId = FinancialTag::TRANSFERENCIA_ID;

        $q = \DB::table('financial_transactions')
            ->leftJoin('financial_credit_card_invoices', 'financial_transactions.financial_credit_card_invoice_id', '=', 'financial_credit_card_invoices.id')
            ->whereNull('financial_transactions.deleted_at')
            ->whereNotIn('financial_transactions.id', function ($sub) use ($transferTagId) {
                $sub->select('financial_taggable_id')
                    ->from('financial_taggables')
                    ->where('financial_taggable_type', FinancialTransaction::class)
                    ->where('financial_tag_id', $transferTagId);
            });

        if (! empty($accountIds)) {
            $q->where(function ($sub) use ($accountIds) {
                $sub->whereIn('financial_transactions.financial_account_id', $accountIds)
                    ->orWhereExists(function ($ex) use ($accountIds) {
                        $ex->select(\DB::raw(1))
                            ->from('financial_credit_cards')
                            ->whereColumn('financial_credit_cards.id', 'financial_credit_card_invoices.financial_credit_card_id')
                            ->whereIn('financial_credit_cards.financial_account_id', $accountIds);
                    });
            });
        }

        return (float) $q->whereRaw('COALESCE(financial_credit_card_invoices.due_date, financial_transactions.date) < ?', [$startDate])
            ->sum(\DB::raw("CASE WHEN financial_transactions.type = 'income' THEN amount WHEN financial_transactions.type = 'expense' THEN -amount ELSE 0 END"));
    }
}

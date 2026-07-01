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
    public function getKpiNumbers(Carbon $referenceDate): array
    {
        $startOfMonth = $referenceDate->copy()->startOfMonth();
        $endOfMonth = $referenceDate->copy()->endOfMonth();

        // Saldo Geral Líquido: Saldo Real das contas - Faturas em Aberto
        $accountBalance = FinancialAccount::withBalance()->get()->sum('balance');
        $creditCardDebt = FinancialCreditCard::with('invoices')->get()->sum(fn ($card) => $card->usedLimit());
        $netBalance = $accountBalance - $creditCardDebt;

        // Total de Receitas: is_posted = true, type = income, s/ transferência
        $income = FinancialTransaction::posted()
            ->where('type', 'income')
            ->whereDoesntHave('tags', fn ($q) => $q->where('financial_tag_id', FinancialTag::TRANSFERENCIA_ID))
            ->sum('amount');

        // Total de Despesas: is_posted = true, type = expense, s/ transferência
        $expense = FinancialTransaction::posted()
            ->where('type', 'expense')
            ->whereDoesntHave('tags', fn ($q) => $q->where('financial_tag_id', FinancialTag::TRANSFERENCIA_ID))
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

        $accountBalance = FinancialAccount::withBalance()->get()->sum('balance');

        // --- Mês Atual (Projeção) ---
        // Receitas Pendentes do Mês
        $pendingIncomeCurrent = FinancialTransaction::whereBetween('date', [$startOfMonth, $endOfMonth])
            ->pending()
            ->where('type', 'income')
            ->whereNull('financial_credit_card_invoice_id')
            ->whereDoesntHave('tags', fn ($q) => $q->where('financial_tag_id', FinancialTag::TRANSFERENCIA_ID))
            ->sum('amount');

        // Despesas Pendentes do Mês (Transações pendentes)
        $pendingExpenseCurrent = FinancialTransaction::whereBetween('date', [$startOfMonth, $endOfMonth])
            ->pending()
            ->where('type', 'expense')
            ->whereNull('financial_credit_card_invoice_id')
            ->whereDoesntHave('tags', fn ($q) => $q->where('financial_tag_id', FinancialTag::TRANSFERENCIA_ID))
            ->sum('amount');

        // Despesas Pendentes: Faturas em aberto a vencer no mês atual
        $openInvoicesCurrent = FinancialCreditCardInvoice::whereBetween('due_date', [$startOfMonth, $endOfMonth])
            ->whereNull('paid_at')
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

        // --- Próximo Mês (Projeção) ---
        $pendingIncomeNext = FinancialTransaction::whereBetween('date', [$nextMonthStart, $nextMonthEnd])
            ->pending()
            ->where('type', 'income')
            ->whereNull('financial_credit_card_invoice_id')
            ->whereDoesntHave('tags', fn ($q) => $q->where('financial_tag_id', FinancialTag::TRANSFERENCIA_ID))
            ->sum('amount');

        $pendingExpenseNext = FinancialTransaction::whereBetween('date', [$nextMonthStart, $nextMonthEnd])
            ->pending()
            ->where('type', 'expense')
            ->whereNull('financial_credit_card_invoice_id')
            ->whereDoesntHave('tags', fn ($q) => $q->where('financial_tag_id', FinancialTag::TRANSFERENCIA_ID))
            ->sum('amount');

        $openInvoicesNext = FinancialCreditCardInvoice::whereBetween('due_date', [$nextMonthStart, $nextMonthEnd])
            ->whereNull('paid_at')
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
        return FinancialCreditCard::all()->map(function ($card) use ($referenceDate) {
            $currentInvoice = FinancialCreditCardInvoice::resolveForDate($card, $referenceDate);
            $usedLimit = $card->usedLimit();
            $availableLimit = max(0, $card->credit_limit - $usedLimit);
            $total = $currentInvoice->total();
            $status = $currentInvoice->status();

            return (object) [
                'id' => $card->id,
                'name' => $card->name,
                'limit' => $card->credit_limit,
                'used' => $usedLimit,
                'available' => $availableLimit,
                'invoice_total' => $total,
                'invoice_due_date' => $currentInvoice->due_date,
                'invoice_closing_date' => $currentInvoice->closing_date,
                'status' => $status,
                'invoice_id' => $currentInvoice->id,
            ];
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
                'type_label' => 'Recorrência (' . ucfirst($r->frequency) . ')',
                'title' => $r->title,
                'amount' => $r->amount,
                'type' => $r->type,
                'date' => $r->next_processing_date,
                'icon' => 'heroicon-o-arrow-path',
            ]);

        $openInvoices = FinancialCreditCardInvoice::with('card')
            ->whereBetween('due_date', [$referenceDate, $endDate])
            ->whereNull('paid_at')
            ->get()
            ->map(fn ($inv) => (object) [
                'type_label' => 'Fatura de Cartão',
                'title' => 'Fatura ' . $inv->card->name,
                'amount' => max(0, $inv->total() - $inv->amount_paid),
                'type' => 'expense',
                'date' => $inv->due_date,
                'icon' => 'heroicon-o-credit-card',
            ]);

        return $pendingTransactions->concat($recurrences)->concat($openInvoices)->sortBy('date')->values();
    }

    public function getTop5Expenses(Carbon $referenceDate): Collection
    {
        $startOfMonth = $referenceDate->copy()->startOfMonth();
        $endOfMonth = $referenceDate->copy()->endOfMonth();

        $expenses = FinancialTransaction::whereBetween('date', [$startOfMonth, $endOfMonth])
            ->posted()
            ->where('type', 'expense')
            ->whereDoesntHave('tags', fn ($q) => $q->where('financial_tag_id', FinancialTag::TRANSFERENCIA_ID))
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
}

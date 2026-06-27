<?php

namespace App\Http\Controllers;

use App\Enums\FinancialAccountType;
use App\Http\Requests\StoreFinancialAccountRequest;
use App\Http\Requests\UpdateFinancialAccountRequest;
use App\Models\FinancialAccount;
use App\Models\FinancialTag;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FinancialAccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $accounts = FinancialAccount::query()
            ->when(request('search'), fn ($query, $search) => $query->search($search, ['name']))
            ->withBalance()
            ->latest()
            ->paginate(18)
            ->withQueryString();

        $hasTrashed = FinancialAccount::onlyTrashed()->exists();

        return view('finance.accounts.index', compact('accounts', 'hasTrashed'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $types = FinancialAccountType::cases();

        $pixKeys = old('pix_keys', []);

        return view('finance.accounts.create', compact('types', 'pixKeys'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreFinancialAccountRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $financialAccount = FinancialAccount::create($validated);

        if (! empty($validated['initial_balance']) && $validated['initial_balance'] != 0) {
            $isPositive = $validated['initial_balance'] > 0;
            $transaction = $financialAccount->transactions()->create([
                'type' => $isPositive ? 'income' : 'expense',
                'amount' => abs($validated['initial_balance']),
                'date' => now(),
                'description' => 'Saldo Inicial',
                'is_posted' => true,
            ]);

            $transaction->tags()->attach(FinancialTag::SALDO_INICIAL_ID, ['is_primary' => true]);
        }

        return redirect()
            ->route('financial.accounts.show', $financialAccount)
            ->with('success', 'Conta financeira criada com sucesso.');
    }

    /**
     * Display the specified resource.
     */
    public function show(FinancialAccount $financialAccount): View
    {
        $account = FinancialAccount::withBalance()->findOrFail($financialAccount->id);

        $globalIncome = $account->transactions()
            ->where('type', 'income')
            ->where('is_posted', true)
            ->sum('amount');

        $globalExpense = $account->transactions()
            ->where('type', 'expense')
            ->where('is_posted', true)
            ->sum('amount');

        $creditCards = $account->creditCards()
            ->with(['invoices' => function ($query) {
                $query->whereNull('paid_at')->orderBy('due_date', 'asc');
            }])
            ->get();

        $recentTransactions = $account->transactions()
            ->latest('date')
            ->latest('id')
            ->limit(10)
            ->get();

        return view('finance.accounts.show', compact(
            'account',
            'globalIncome',
            'globalExpense',
            'creditCards',
            'recentTransactions'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(FinancialAccount $financialAccount): View
    {
        $types = FinancialAccountType::cases();
        $pixKeys = old('pix_keys', $financialAccount->pix_keys ?? []);

        return view('finance.accounts.edit', compact('financialAccount', 'types', 'pixKeys'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFinancialAccountRequest $request, FinancialAccount $financialAccount): RedirectResponse
    {
        $financialAccount->update($request->validated());

        return redirect()
            ->route('financial.accounts.show', $financialAccount)
            ->with('success', 'Conta financeira atualizada com sucesso.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FinancialAccount $financialAccount): RedirectResponse
    {
        $financialAccount->delete();

        return redirect()
            ->route('financial.accounts.index')
            ->with('success', 'Conta financeira movida para a lixeira.');
    }

    /**
     * Display a listing of trashed resources.
     */
    public function trashed(): View
    {
        $accounts = FinancialAccount::onlyTrashed()
            ->when(request('search'), fn ($query, $search) => $query->search($search, ['name']))
            ->latest('deleted_at')
            ->paginate(50)
            ->withQueryString();

        return view('finance.accounts.trashed', compact('accounts'));
    }

    /**
     * Restore a trashed resource.
     */
    public function restore(FinancialAccount $financialAccount): RedirectResponse
    {
        $financialAccount->restore();

        return redirect()
            ->route('financial.accounts.show', $financialAccount)
            ->with('success', 'Conta financeira restaurada com sucesso.');
    }

    /**
     * Permanently delete a trashed resource.
     */
    public function forceDestroy(FinancialAccount $financialAccount): RedirectResponse
    {
        if ($financialAccount->creditCards()->exists() ||
            $financialAccount->transactions()->exists() ||
            $financialAccount->recurrences()->exists()) {
            return redirect()
                ->route('financial.accounts.trashed')
                ->with('error', 'Não é possível excluir permanentemente esta conta pois ela possui cartões de crédito, transações ou recorrências vinculadas. Remova os vínculos primeiro.');
        }

        $financialAccount->forceDelete();

        return redirect()
            ->route('financial.accounts.trashed')
            ->with('success', 'Conta financeira excluída permanentemente.');
    }
}

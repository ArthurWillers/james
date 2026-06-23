<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFinancialTagRequest;
use App\Http\Requests\UpdateFinancialTagRequest;
use App\Models\FinancialTag;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FinancialTagController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $tags = FinancialTag::query()
            ->when(request('search'), fn ($query, $search) => $query->search($search, ['name']))
            ->orderBy('name')
            ->paginate(18)
            ->withQueryString();

        return view('finance.tags.index', compact('tags'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('finance.tags.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreFinancialTagRequest $request): RedirectResponse
    {
        $financialTag = FinancialTag::create($request->validated());

        return redirect()
            ->route('financial.tags.index')
            ->with('success', 'Tag criada com sucesso.');
    }

    /**
     * Display the specified resource.
     */
    public function show(FinancialTag $financialTag): View
    {
        return view('finance.tags.show', compact('financialTag'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(FinancialTag $financialTag): View|RedirectResponse
    {
        if ($financialTag->is_protected) {
            return redirect()
                ->route('financial.tags.index')
                ->with('error', 'Tags protegidas não podem ser editadas.');
        }

        return view('finance.tags.edit', compact('financialTag'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFinancialTagRequest $request, FinancialTag $financialTag): RedirectResponse
    {
        $financialTag->update($request->validated());

        return redirect()
            ->route('financial.tags.index')
            ->with('success', 'Tag atualizada com sucesso.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FinancialTag $financialTag): RedirectResponse
    {
        if ($financialTag->is_protected) {
            abort(403, 'Tags protegidas não podem ser excluídas.');
        }

        if ($financialTag->transactions()->exists() || $financialTag->transactionItems()->exists()) {
            return redirect()
                ->route('financial.tags.index')
                ->with('error', 'Esta tag não pode ser excluída pois está em uso.');
        }

        $financialTag->delete();

        return redirect()
            ->route('financial.tags.index')
            ->with('success', 'Tag excluída com sucesso.');
    }
}

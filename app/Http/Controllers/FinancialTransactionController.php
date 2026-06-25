<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFinancialTransactionRequest;
use App\Http\Requests\StoreFinancialTransferRequest;
use App\Http\Requests\UpdateFinancialTransactionRequest;
use App\Models\FinancialAccount;
use App\Models\FinancialCreditCard;
use App\Models\FinancialCreditCardInvoice;
use App\Models\FinancialTag;
use App\Models\FinancialTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class FinancialTransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = FinancialTransaction::query()->with(['account', 'invoice.creditCard', 'tags']);

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('account_id')) {
            $query->where('financial_account_id', $request->account_id);
        }

        if ($request->filled('credit_card_invoice_id')) {
            $query->where('financial_credit_card_invoice_id', $request->credit_card_invoice_id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('is_posted')) {
            $query->where('is_posted', $request->boolean('is_posted'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        if ($request->filled('tag_id')) {
            $query->whereHas('tags', function ($q) use ($request) {
                $q->where('financial_tags.id', $request->tag_id);
            });
        }

        $transactions = $query->orderBy('date', 'desc')->paginate(25)->withQueryString();

        $accounts = FinancialAccount::all();
        $tags = FinancialTag::all();

        return view('finance.transactions.index', compact('transactions', 'accounts', 'tags'));
    }

    public function create()
    {
        $accounts = FinancialAccount::all();
        $cards = FinancialCreditCard::all();
        $tags = FinancialTag::all()->map(function ($tag) {
            return [
                'id' => $tag->id,
                'name' => $tag->name,
                'color_hex' => $tag->color_hex,
                'svg' => \Illuminate\Support\Facades\Blade::render('<x-dynamic-component :component="$icon" class="size-3.5" />', ['icon' => $tag->icon])
            ];
        });

        return view('finance.transactions.create', compact('accounts', 'cards', 'tags'));
    }

    public function store(StoreFinancialTransactionRequest $request)
    {
        $validated = $request->validated();
        $mode = $validated['mode'];

        if ($mode === 'single') {
            if (! empty($validated['financial_credit_card_id'])) {
                $card = FinancialCreditCard::findOrFail($validated['financial_credit_card_id']);
                $date = Carbon::parse($validated['date']);
                $invoice = FinancialCreditCardInvoice::resolveForDate($card, $date);

                $transaction = $invoice->transactions()->create([
                    'type' => $validated['type'],
                    'amount' => $validated['amount'],
                    'description' => $validated['description'],
                    'date' => $date,
                    'is_posted' => false,
                ]);
            } else {
                $transaction = FinancialTransaction::create([
                    'financial_account_id' => $validated['financial_account_id'],
                    'type' => $validated['type'],
                    'amount' => $validated['amount'],
                    'description' => $validated['description'],
                    'date' => Carbon::parse($validated['date']),
                    'is_posted' => Carbon::parse($validated['date'])->startOfDay()->lte(Carbon::today()),
                ]);
            }

            $globalTags = $validated['tags'] ?? [];
            $globalPrimaryId = empty($validated['items']) ? ($validated['primary_tag_id'] ?? null) : null;
            $this->syncTagsWithPrimary($transaction, $globalTags, $globalPrimaryId);

            if (!empty($validated['items'])) {
                foreach ($validated['items'] as $itemData) {
                    $item = $transaction->items()->create([
                        'description' => $itemData['description'],
                        'quantity' => $itemData['quantity'],
                        'unit_price' => $itemData['unit_price'],
                    ]);
                    $this->syncTagsWithPrimary($item, $itemData['tags'] ?? [], $itemData['primary_tag_id'] ?? null);
                }
            }
        } elseif ($mode === 'installment') {
            if (! empty($validated['financial_credit_card_id'])) {
                $card = FinancialCreditCard::findOrFail($validated['financial_credit_card_id']);
                $card->createInstallmentPurchase(
                    Carbon::parse($validated['date']),
                    $validated['amount'],
                    $validated['installments'],
                    $validated['description'],
                    $validated['tags'] ?? null
                );
            } else {
                $account = FinancialAccount::findOrFail($validated['financial_account_id']);
                $transactions = FinancialTransaction::createInstallmentsOnAccount(
                    $account,
                    Carbon::parse($validated['date']),
                    $validated['amount'],
                    $validated['installments'],
                    $validated['description'],
                    $validated['type']
                );

                $globalTags = $validated['tags'] ?? [];
                $globalPrimaryId = empty($validated['items']) ? ($validated['primary_tag_id'] ?? null) : null;

                if (! empty($validated['tags']) || ! empty($validated['items'])) {
                    foreach ($transactions as $t) {
                        $this->syncTagsWithPrimary($t, $globalTags, $globalPrimaryId);
                        
                        if (!empty($validated['items'])) {
                            foreach ($validated['items'] as $itemData) {
                                $item = $t->items()->create([
                                    'description' => $itemData['description'],
                                    'quantity' => $itemData['quantity'],
                                    'unit_price' => $itemData['unit_price'],
                                ]);
                                $this->syncTagsWithPrimary($item, $itemData['tags'] ?? [], $itemData['primary_tag_id'] ?? null);
                            }
                        }
                    }
                }
            }
        }

        return redirect()->route('financial.transactions.index')->with('success', 'Transação criada com sucesso.');
    }

    public function createTransfer()
    {
        $accounts = FinancialAccount::all();
        $tags = FinancialTag::all();

        return view('finance.transactions.transfer', compact('accounts', 'tags'));
    }

    public function storeTransfer(StoreFinancialTransferRequest $request)
    {
        $validated = $request->validated();

        $from = FinancialAccount::findOrFail($validated['from_account_id']);
        $to = FinancialAccount::findOrFail($validated['to_account_id']);

        $transactions = FinancialTransaction::createTransfer(
            $from,
            $to,
            $validated['amount'],
            Carbon::parse($validated['date']),
            $validated['description'],
            $validated['fee_amount'] ?? null,
            $validated['fee_tag_id'] ?? null
        );

        return redirect()->route('financial.transactions.index')->with('success', 'Transferência criada com sucesso.');
    }

    public function show(FinancialTransaction $transaction)
    {
        $transaction->load(['account', 'invoice.creditCard', 'tags', 'items.tags', 'transferPair.account']);

        return view('finance.transactions.show', compact('transaction'));
    }

    public function edit(FinancialTransaction $transaction)
    {
        $accounts = FinancialAccount::all();
        $tags = FinancialTag::all()->map(function ($tag) {
            return [
                'id' => $tag->id,
                'name' => $tag->name,
                'color_hex' => $tag->color_hex,
                'svg' => \Illuminate\Support\Facades\Blade::render('<x-dynamic-component :component="$icon" class="size-3.5" />', ['icon' => $tag->icon])
            ];
        });

        return view('finance.transactions.edit', compact('transaction', 'accounts', 'tags'));
    }

    public function update(UpdateFinancialTransactionRequest $request, FinancialTransaction $transaction)
    {
        $transaction->update($request->validated());

        $globalTags = $request->tags ?? [];
        $globalPrimaryId = !$request->has('items') ? ($request->primary_tag_id ?? null) : null;
        $this->syncTagsWithPrimary($transaction, $globalTags, $globalPrimaryId);
        
        if ($request->has('items')) {
            $transaction->items()->delete();
            foreach ($request->items as $itemData) {
                $item = $transaction->items()->create([
                    'description' => $itemData['description'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                ]);
                $this->syncTagsWithPrimary($item, $itemData['tags'] ?? [], $itemData['primary_tag_id'] ?? null);
            }
        } else {
            $transaction->items()->delete();
        }

        return redirect()->route('financial.transactions.show', $transaction)->with('success', 'Transação atualizada com sucesso.');
    }

    public function destroy(FinancialTransaction $transaction)
    {
        $transaction->delete();

        return redirect()->route('financial.transactions.index')->with('success', 'Transação movida para a lixeira com sucesso.');
    }

    private function syncTagsWithPrimary($model, array $tags, $primaryId)
    {
        if (empty($tags)) {
            $model->tags()->detach();
            return;
        }

        $syncData = [];
        foreach ($tags as $tagId) {
            $syncData[$tagId] = ['is_primary' => ($tagId == $primaryId)];
        }
        $model->tags()->sync($syncData);
    }
}

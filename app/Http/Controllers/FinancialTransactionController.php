<?php

namespace App\Http\Controllers;

use App\Enums\TransactionStatus;
use App\Http\Requests\StoreFinancialTransactionRequest;
use App\Http\Requests\StoreFinancialTransferRequest;
use App\Http\Requests\StoreNfceImportRequest;
use App\Http\Requests\UpdateFinancialTransactionRequest;
use App\Jobs\ScrapeNfceInvoiceJob;
use App\Models\FinancialAccount;
use App\Models\FinancialCreditCard;
use App\Models\FinancialCreditCardInvoice;
use App\Models\FinancialTag;
use App\Models\FinancialTransaction;
use App\Models\SettlementGroup;
use App\Services\Nfce\Exceptions\InvalidNfceUrlException;
use App\Services\Nfce\Exceptions\UnsupportedNfceProviderException;
use App\Services\Nfce\NfceSourceResolver;
use App\Traits\HandlesAttachments;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Blade;

class FinancialTransactionController extends Controller
{
    use HandlesAttachments;

    public function index(Request $request)
    {
        $query = FinancialTransaction::query()->with(['account', 'invoice.creditCard', 'tags']);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->search($request->search, ['description'])
                    ->orWhereHas('items', function ($q2) use ($request) {
                        $q2->whereRaw('unaccent(description) ILIKE unaccent(?)', ["%{$request->search}%"]);
                    });
            });
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

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }

        if ($request->filled('tag_id')) {
            $query->whereHas('tags', function ($q) use ($request) {
                $q->where('financial_tags.id', $request->tag_id);
            });
        }

        $transactions = $query->orderBy('date', 'desc')->orderBy('updated_at', 'desc')->paginate(25)->withQueryString();

        $accounts = FinancialAccount::orderBy('name')->get();
        $tags = FinancialTag::orderBy('name')->get();
        $hasTrashed = FinancialTransaction::onlyTrashed()->exists();

        return view('finance.transactions.index', compact('transactions', 'accounts', 'tags', 'hasTrashed'));
    }

    public function create()
    {
        $accounts = FinancialAccount::orderBy('name')->get();
        $cards = FinancialCreditCard::orderBy('name')->get();
        $tags = FinancialTag::orderBy('name')->get()->map(function ($tag) {
            return [
                'id' => $tag->id,
                'name' => $tag->name,
                'color_hex' => $tag->color_hex,
                'svg' => Blade::render('<x-dynamic-component :component="$icon" class="size-3.5" />', ['icon' => $tag->icon]),
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
                    'status' => TransactionStatus::Pending,
                ]);
            } else {
                $transaction = FinancialTransaction::create([
                    'financial_account_id' => $validated['financial_account_id'],
                    'type' => $validated['type'],
                    'amount' => $validated['amount'],
                    'description' => $validated['description'],
                    'date' => Carbon::parse($validated['date']),
                    'status' => $validated['status'] ?? TransactionStatus::Posted,
                ]);
            }

            $hasItemTags = false;
            if (! empty($validated['items'])) {
                foreach ($validated['items'] as $itemData) {
                    if (! empty($itemData['tags'])) {
                        $hasItemTags = true;
                        break;
                    }
                }
            }

            $globalTags = $validated['tags'] ?? [];
            $globalPrimaryId = $hasItemTags ? null : ($validated['primary_tag_id'] ?? null);
            $this->syncTagsWithPrimary($transaction, $globalTags, $globalPrimaryId);

            if (! empty($validated['items'])) {
                foreach ($validated['items'] as $itemData) {
                    $item = $transaction->items()->create([
                        'description' => $itemData['description'],
                        'quantity' => $itemData['quantity'],
                        'unit_price' => $itemData['unit_price'],
                        'total' => $itemData['quantity'] * $itemData['unit_price'],
                    ]);
                    $this->syncTagsWithPrimary($item, $itemData['tags'] ?? [], $itemData['primary_tag_id'] ?? null);
                }
            }
        } elseif ($mode === 'installment') {
            if (! empty($validated['financial_credit_card_id'])) {
                $card = FinancialCreditCard::findOrFail($validated['financial_credit_card_id']);
                $transactions = $card->createInstallmentPurchase(
                    Carbon::parse($validated['date']),
                    $validated['amount'],
                    $validated['installments'],
                    $validated['description']
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
            }

            $hasItemTags = false;
            if (! empty($validated['items'])) {
                foreach ($validated['items'] as $itemData) {
                    if (! empty($itemData['tags'])) {
                        $hasItemTags = true;
                        break;
                    }
                }
            }

            $globalTags = $validated['tags'] ?? [];
            $globalPrimaryId = $hasItemTags ? null : ($validated['primary_tag_id'] ?? null);

            if (! empty($validated['tags']) || ! empty($validated['items'])) {
                foreach ($transactions as $t) {
                    $this->syncTagsWithPrimary($t, $globalTags, $globalPrimaryId);

                    if (! empty($validated['items'])) {
                        $installmentItemsTotalCents = 0;

                        foreach ($validated['items'] as $itemData) {
                            $originalUnitPriceCents = (int) round($itemData['unit_price'] * 100);
                            $unitPriceInstallmentCents = (int) floor($originalUnitPriceCents / $validated['installments']);

                            $isLastInstallment = $t->installment_current === $t->installment_total;
                            if ($isLastInstallment) {
                                $unitPriceRemainderCents = $originalUnitPriceCents - ($unitPriceInstallmentCents * $validated['installments']);
                                $unitPriceInstallmentCents += $unitPriceRemainderCents;
                            }

                            $currentUnitPrice = round($unitPriceInstallmentCents / 100, 2);
                            $currentItemTotal = round($itemData['quantity'] * $currentUnitPrice, 2);
                            $installmentItemsTotalCents += (int) round($currentItemTotal * 100);

                            $item = $t->items()->create([
                                'description' => $itemData['description'],
                                'quantity' => $itemData['quantity'],
                                'unit_price' => $currentUnitPrice,
                                'total' => $currentItemTotal,
                            ]);
                            $this->syncTagsWithPrimary($item, $itemData['tags'] ?? [], $itemData['primary_tag_id'] ?? null);
                        }

                        // Update the transaction amount to exactly match the sum of its items
                        // This prevents 1-cent inconsistencies between the transaction amount and the items total
                        $t->update(['amount' => round($installmentItemsTotalCents / 100, 2)]);
                    }
                }
            }

            $firstTransaction = $transactions->first();
            if ($firstTransaction) {
                $this->syncAttachments($firstTransaction, $request->all());
            }
        }

        if ($mode === 'single' && isset($transaction)) {
            $this->syncAttachments($transaction, $request->all());
        }

        return redirect()->route('financial.transactions.index')->with('success', 'Transação criada com sucesso.');
    }

    public function importNfce(StoreNfceImportRequest $request, NfceSourceResolver $sourceResolver)
    {
        $validated = $request->validated();

        try {
            $source = $sourceResolver->resolve($validated['url']);
        } catch (InvalidNfceUrlException|UnsupportedNfceProviderException $exception) {
            return back()->withErrors(['url' => $exception->getMessage()])->withInput();
        }

        if (FinancialTransaction::withTrashed()->where('nfce_access_key', $source->accessKey)->exists()) {
            return back()->withErrors(['url' => 'Esta NFC-e já foi importada.'])->withInput();
        }

        ScrapeNfceInvoiceJob::dispatch(
            requesterId: $request->user()->id,
            provider: $source->provider,
            accessKey: $source->accessKey,
            uf: $source->uf,
            sourceEndpoint: $source->sourceEndpoint,
            requestParameterSuffix: $source->requestParameterSuffix,
        );

        return redirect()->route('financial.transactions.index')
            ->with('success', 'Importação enviada para processamento. Você será notificado quando terminar.');
    }

    public function storeTransfer(StoreFinancialTransferRequest $request)
    {
        $validated = $request->validated();

        $from = FinancialAccount::findOrFail($validated['from_account_id']);
        $to = FinancialAccount::findOrFail($validated['to_account_id']);

        FinancialTransaction::createTransfer(
            $from,
            $to,
            $validated['amount'],
            Carbon::parse($validated['date']),
            $validated['description'],
            $validated['fee_amount'] ?? null,
            ! empty($validated['fee_amount']) ? FinancialTag::JUROS_ID : null
        );

        return redirect()->route('financial.transactions.index')->with('success', 'Transferência criada com sucesso.');
    }

    public function show(FinancialTransaction $transaction)
    {
        $transaction->load(['account', 'invoice.creditCard', 'tags', 'items.tags', 'settlements']);

        $settlementGroup = SettlementGroup::where('financial_transaction_id', $transaction->id)->first();

        $isSettlementTransaction = $settlementGroup || $transaction->settlements->isNotEmpty();

        $editRoute = route('financial.transactions.edit', $transaction->id);
        if ($settlementGroup) {
            $editRoute = route('settlements.groups.edit', $settlementGroup);
        } elseif ($transaction->settlements->isNotEmpty()) {
            $editRoute = route('settlements.edit', $transaction->settlements->first());
        }

        return view('finance.transactions.show', compact('transaction', 'settlementGroup', 'isSettlementTransaction', 'editRoute'));
    }

    public function edit(FinancialTransaction $transaction)
    {
        $transaction->load(['tags', 'items.tags', 'invoice.creditCard']);
        $accounts = FinancialAccount::orderBy('name')->get();
        $cards = FinancialCreditCard::orderBy('name')->get();
        $tags = FinancialTag::orderBy('name')->get()->map(function ($tag) {
            return [
                'id' => $tag->id,
                'name' => $tag->name,
                'color_hex' => $tag->color_hex,
                'svg' => Blade::render('<x-dynamic-component :component="$icon" class="size-3.5" />', ['icon' => $tag->icon]),
            ];
        });

        return view('finance.transactions.edit', compact('transaction', 'accounts', 'cards', 'tags'));
    }

    public function update(UpdateFinancialTransactionRequest $request, FinancialTransaction $transaction)
    {
        $validated = $request->validated();

        if (! empty($validated['financial_credit_card_id'])) {
            $card = FinancialCreditCard::findOrFail($validated['financial_credit_card_id']);
            $date = Carbon::parse($validated['date']);

            $invoiceDate = $date->copy();
            if ($transaction->installment_current > 1) {
                $invoiceDate->addMonthsNoOverflow($transaction->installment_current - 1);
            }

            $invoice = FinancialCreditCardInvoice::resolveForDate($card, $invoiceDate);

            $transaction->update([
                'financial_account_id' => null,
                'financial_credit_card_invoice_id' => $invoice->id,
                'type' => $validated['type'],
                'amount' => $validated['amount'],
                'description' => $validated['description'],
                'date' => $date,
            ]);
        } else {
            $date = Carbon::parse($validated['date']);
            $transaction->update([
                'financial_account_id' => $validated['financial_account_id'],
                'financial_credit_card_invoice_id' => null,
                'type' => $validated['type'],
                'amount' => $validated['amount'],
                'description' => $validated['description'],
                'date' => $date,
                'status' => $validated['status'] ?? TransactionStatus::Posted,
            ]);
        }

        $hasItemTags = false;
        if (! empty($validated['items'])) {
            foreach ($validated['items'] as $itemData) {
                if (! empty($itemData['tags'])) {
                    $hasItemTags = true;
                    break;
                }
            }
        }

        $globalTags = $validated['tags'] ?? [];
        $globalPrimaryId = $hasItemTags ? null : ($validated['primary_tag_id'] ?? null);
        $this->syncTagsWithPrimary($transaction, $globalTags, $globalPrimaryId);

        if ($request->has('items')) {
            $transaction->items()->delete();
            foreach ($validated['items'] as $itemData) {
                $item = $transaction->items()->create([
                    'description' => $itemData['description'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'total' => $itemData['quantity'] * $itemData['unit_price'],
                ]);
                $this->syncTagsWithPrimary($item, $itemData['tags'] ?? [], $itemData['primary_tag_id'] ?? null);
            }
        } else {
            $transaction->items()->delete();
        }

        $this->syncAttachments($transaction, $validated);

        return redirect()->route('financial.transactions.show', $transaction)->with('success', 'Transação atualizada com sucesso.');
    }

    public function destroy(FinancialTransaction $transaction)
    {
        $transaction->delete();

        return redirect()->route('financial.transactions.index')->with('success', 'Transação movida para a lixeira.');
    }

    public function trashed(Request $request)
    {
        $transactions = FinancialTransaction::onlyTrashed()
            ->with([
                'account',
                'invoice.creditCard',
                'tags',
                'settlements' => fn ($q) => $q->withTrashed(),
                'settlementGroup' => fn ($q) => $q->withTrashed(),
            ])
            ->orderByDesc('date')
            ->orderByDesc('updated_at')
            ->paginate(15);

        return view('finance.transactions.trashed', compact('transactions'));
    }

    public function restore(FinancialTransaction $transaction)
    {
        $transaction->restore();

        return redirect()->back()->with('success', 'Transação restaurada com sucesso.');
    }

    public function forceDestroy(FinancialTransaction $transaction)
    {
        $transaction->forceDelete();

        return redirect()->back()->with('success', 'Transação excluída permanentemente.');
    }

    private function syncTagsWithPrimary(mixed $model, array $tags, ?int $primaryId)
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

    public function attachment(FinancialTransaction $transaction, $mediaId)
    {
        $media = $transaction->getMedia('attachments')->where('id', $mediaId)->first();

        if (! $media) {
            abort(404);
        }

        return response()->file($media->getPath(), [
            'Content-Disposition' => 'inline; filename="'.$media->file_name.'"',
        ]);
    }
}

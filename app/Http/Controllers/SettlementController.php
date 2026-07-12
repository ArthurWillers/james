<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSettlementRequest;
use App\Http\Requests\UpdateSettlementRequest;
use App\Models\Settlement;
use App\Models\Contact;
use App\Models\ContactGroup;
use App\Enums\SettlementType;
use Illuminate\Http\Request;

class SettlementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $showArchived = $request->boolean('archived');

        $theyOweSql = "(SELECT COALESCE(SUM(amount), 0) FROM settlements WHERE contact_id = contacts.id AND type = '" . SettlementType::TheyOwe->value . "' AND deleted_at IS NULL)";
        $theyPaidSql = "(SELECT COALESCE(SUM(amount), 0) FROM settlements WHERE contact_id = contacts.id AND type = '" . SettlementType::TheyPaid->value . "' AND deleted_at IS NULL)";
        
        $iOweSql = "(SELECT COALESCE(SUM(amount), 0) FROM settlements WHERE contact_id = contacts.id AND type = '" . SettlementType::IOwe->value . "' AND deleted_at IS NULL)";
        $iPaidSql = "(SELECT COALESCE(SUM(amount), 0) FROM settlements WHERE contact_id = contacts.id AND type = '" . SettlementType::IPaid->value . "' AND deleted_at IS NULL)";

        $toReceiveSql = "GREATEST(0, $theyOweSql - $theyPaidSql)";
        $toPaySql = "GREATEST(0, $iOweSql - $iPaidSql)";
        
        $netBalanceSql = "($toReceiveSql - $toPaySql)";
        $settlementsCountSql = "(SELECT COUNT(*) FROM settlements WHERE contact_id = contacts.id AND deleted_at IS NULL)";

        $contacts = Contact::with(['groups', 'media'])
            ->when($showArchived, function ($query) {
                $query->whereHas('settlementArchive');
            }, function ($query) {
                $query->notSettlementArchived();
            })
            ->withSum(['settlements as they_owe' => fn ($q) => $q->where('type', SettlementType::TheyOwe->value)], 'amount')
            ->withSum(['settlements as they_paid' => fn ($q) => $q->where('type', SettlementType::TheyPaid->value)], 'amount')
            ->withSum(['settlements as i_owe' => fn ($q) => $q->where('type', SettlementType::IOwe->value)], 'amount')
            ->withSum(['settlements as i_paid' => fn ($q) => $q->where('type', SettlementType::IPaid->value)], 'amount')
            ->withCount('settlements')
            ->orderByRaw("
                CASE 
                    WHEN $netBalanceSql > 0 THEN 3
                    WHEN $netBalanceSql < 0 THEN 2
                    WHEN $settlementsCountSql > 0 THEN 1
                    ELSE 0
                END DESC
            ")
            ->orderByRaw("
                CASE 
                    WHEN $netBalanceSql > 0 THEN $netBalanceSql
                    WHEN $netBalanceSql < 0 THEN ABS($netBalanceSql)
                    ELSE 0
                END DESC
            ")
            ->get()
            ->map(function ($contact) {
                $toReceive = max(0, ($contact->they_owe ?? 0) - ($contact->they_paid ?? 0));
                $toPay = max(0, ($contact->i_owe ?? 0) - ($contact->i_paid ?? 0));
                
                $contact->to_receive = $toReceive;
                $contact->to_pay = $toPay;
                $contact->net_balance = $toReceive - $toPay;
                $contact->avatar_url = $contact->avatar;
                // Add group_ids for filtering in Alpine
                $contact->group_ids = $contact->groups->pluck('id')->toArray();
                return $contact;
            })
            ->values();

        $toReceive = $contacts->sum('to_receive') ?? 0;
        $toPay = $contacts->sum('to_pay') ?? 0;
        $netBalance = $toReceive - $toPay;

        $groups = ContactGroup::orderBy('name')->get();

        return view('settlements.index', compact('contacts', 'toReceive', 'toPay', 'netBalance', 'showArchived', 'groups'));
    }

    /**
     * Display a global history of settlements.
     */
    public function history()
    {
        $settlements = Settlement::with(['contact', 'contact.media'])
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate(50);

        return view('settlements.history', compact('settlements'));
    }

    /**
     * Display the ledger for a specific contact.
     */
    public function showContact(Contact $contact)
    {
        // Compute balances for this contact using the max(0, debt - payment) rule
        $debtTheyOweMe = Settlement::where('contact_id', $contact->id)->where('type', SettlementType::TheyOwe->value)->sum('amount');
        $paymentsTheyMade = Settlement::where('contact_id', $contact->id)->where('type', SettlementType::TheyPaid->value)->sum('amount');
        $toReceive = max(0, $debtTheyOweMe - $paymentsTheyMade);

        $debtIOweThem = Settlement::where('contact_id', $contact->id)->where('type', SettlementType::IOwe->value)->sum('amount');
        $paymentsIMade = Settlement::where('contact_id', $contact->id)->where('type', SettlementType::IPaid->value)->sum('amount');
        $toPay = max(0, $debtIOweThem - $paymentsIMade);
            
        $netBalance = $toReceive - $toPay;

        $settlements = Settlement::where('contact_id', $contact->id)
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate(50);

        return view('settlements.show', compact('contact', 'settlements', 'toReceive', 'toPay', 'netBalance'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Contact $contact)
    {
        $accounts = \App\Models\FinancialAccount::all();
        $cards = \App\Models\FinancialCreditCard::all();
        
        return view('settlements.create', compact('contact', 'accounts', 'cards'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSettlementRequest $request, Contact $contact)
    {
        $validated = $request->validated();
        
        $settlement = new Settlement();
        $settlement->contact_id = $contact->id;
        $settlement->type = $validated['type'];
        $settlement->amount = $validated['amount'];
        $settlement->description = $validated['description'];
        $settlement->date = \Illuminate\Support\Carbon::parse($validated['date']);
        
        // Transaction logic
        if (!empty($validated['create_transaction'])) {
            $transaction = $this->createOrUpdateTransaction(null, $validated);
            if ($transaction) {
                $settlement->financial_transaction_id = $transaction->id;
            }
        }
        
        $settlement->save();

        return redirect()->route('settlements.contact.show', $contact)
            ->with('success', 'Lançamento registrado com sucesso.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Settlement $settlement)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Settlement $settlement)
    {
        $contact = $settlement->contact;
        $accounts = \App\Models\FinancialAccount::all();
        $cards = \App\Models\FinancialCreditCard::all();
        
        return view('settlements.edit', compact('settlement', 'contact', 'accounts', 'cards'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSettlementRequest $request, Settlement $settlement)
    {
        $validated = $request->validated();
        
        $settlement->type = $validated['type'];
        $settlement->amount = $validated['amount'];
        $settlement->description = $validated['description'];
        $settlement->date = \Illuminate\Support\Carbon::parse($validated['date']);
        
        // Transaction logic
        if (!empty($validated['create_transaction'])) {
            $transaction = $this->createOrUpdateTransaction($settlement->financialTransaction, $validated);
            $settlement->financial_transaction_id = $transaction->id;
        } else {
            if ($settlement->financial_transaction_id) {
                $settlement->financialTransaction()->delete();
                $settlement->financial_transaction_id = null;
            }
        }
        
        $settlement->save();

        return redirect()->route('settlements.contact.show', $settlement->contact_id)
            ->with('success', 'Lançamento atualizado com sucesso.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Settlement $settlement)
    {
        if ($settlement->financialTransaction) {
            $settlement->financialTransaction()->delete();
        }
        
        $contactId = $settlement->contact_id;
        $settlement->delete();
        
        return redirect()->route('settlements.contact.show', $contactId)
            ->with('success', 'Lançamento excluído com sucesso.');
    }
    
    /**
     * Create or update the associated financial transaction.
     */
    private function createOrUpdateTransaction(?\App\Models\FinancialTransaction $transaction, array $validated): ?\App\Models\FinancialTransaction
    {
        // Type translation
        // TheyOwe (I paid) -> expense, IPaid (I paid) -> expense
        // TheyPaid (They paid me) -> income
        $type = 'expense';
        if ($validated['type'] === \App\Enums\SettlementType::TheyPaid->value) {
            $type = 'income';
        }
        
        $date = \Illuminate\Support\Carbon::parse($validated['date']);
        
        $data = [
            'type' => $type,
            'amount' => $validated['amount'],
            'description' => $validated['description'],
            'date' => $date,
            'is_posted' => true,
            'financial_account_id' => null,
            'financial_credit_card_invoice_id' => null,
        ];

        if ($validated['targetType'] === 'card') {
            $card = \App\Models\FinancialCreditCard::findOrFail($validated['financial_credit_card_id']);
            $invoice = \App\Models\FinancialCreditCardInvoice::resolveForDate($card, $date);
            $data['financial_credit_card_invoice_id'] = $invoice->id;
        } else {
            $data['financial_account_id'] = $validated['financial_account_id'];
        }

        if ($transaction) {
            $transaction->update($data);
        } else {
            $transaction = \App\Models\FinancialTransaction::create($data);
        }

        // Sincroniza a tag "Reembolso" (is_primary = true)
        $transaction->tags()->sync([
            \App\Models\FinancialTag::REEMBOLSO_ID => ['is_primary' => true]
        ]);

        return $transaction;
    }
}

<?php

namespace App\Services;

use App\Enums\SettlementType;
use App\Models\Contact;
use App\Models\FinancialCreditCard;
use App\Models\FinancialCreditCardInvoice;
use App\Models\FinancialTag;
use App\Models\FinancialTransaction;
use App\Models\FinancialTransactionItem;
use App\Models\Settlement;
use App\Models\SettlementGroup;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SettlementGroupService
{
    /**
     * Create a new settlement group with its child settlements.
     *
     * @param  array{
     *     description: string,
     *     total_amount: float,
     *     date: string,
     *     mode: string,
     *     contacts: array<int, array{id: int, amount: float}>,
     *     my_amount: float,
     *     create_transaction?: bool,
     *     targetType?: string,
     *     financial_account_id?: int,
     *     financial_credit_card_id?: int,
     *     financial_tag_ids?: array<int>,
     * } $validated
     */
    public function storeGroup(array $validated): SettlementGroup
    {
        return DB::transaction(function () use ($validated) {
            $date = Carbon::parse($validated['date']);
            $financialTransactionId = null;

            // 1. Create FinancialTransaction if requested
            if (!empty($validated['create_transaction'])) {
                $transaction = $this->createTransaction($validated, $date);
                $financialTransactionId = $transaction->id;
            }

            // 2. Create the SettlementGroup
            $group = SettlementGroup::create([
                'description' => $validated['description'],
                'total_amount' => $validated['total_amount'],
                'date' => $date,
                'mode' => $validated['mode'],
                'financial_transaction_id' => $financialTransactionId,
            ]);

            // 3. Create child settlements for each contact
            foreach ($validated['contacts'] as $contactData) {
                $contact = Contact::findOrFail($contactData['id']);

                Settlement::create([
                    'contact_id' => $contact->id,
                    'settlement_group_id' => $group->id,
                    'type' => SettlementType::TheyOwe->value,
                    'amount' => $contactData['amount'],
                    'description' => $validated['description'] . ' - ' . $contact->name,
                    'date' => $date,
                ]);
            }

            return $group;
        });
    }

    /**
     * Update an existing settlement group (wipe & replace children).
     */
    public function updateGroup(SettlementGroup $group, array $validated): SettlementGroup
    {
        return DB::transaction(function () use ($group, $validated) {
            $date = Carbon::parse($validated['date']);

            // 1. Manage FinancialTransaction
            if (!empty($validated['create_transaction'])) {
                $existingTransaction = $group->financialTransaction;

                if ($existingTransaction) {
                    // Delete old items and recreate
                    $existingTransaction->items()->delete();
                    $existingTransaction->forceDelete();
                }

                $transaction = $this->createTransaction($validated, $date);
                $group->financial_transaction_id = $transaction->id;
            } else {
                if ($group->financial_transaction_id) {
                    $existingTransaction = $group->financialTransaction;
                    if ($existingTransaction) {
                        $existingTransaction->items()->delete();
                        $existingTransaction->forceDelete();
                    }
                    $group->financial_transaction_id = null;
                }
            }

            // 2. Update group metadata
            $group->description = $validated['description'];
            $group->total_amount = $validated['total_amount'];
            $group->date = $date;
            $group->mode = $validated['mode'];
            $group->save();

            // 3. Wipe old children
            $group->settlements()->forceDelete();

            // 4. Replace with new children
            foreach ($validated['contacts'] as $contactData) {
                $contact = Contact::findOrFail($contactData['id']);

                Settlement::create([
                    'contact_id' => $contact->id,
                    'settlement_group_id' => $group->id,
                    'type' => SettlementType::TheyOwe->value,
                    'amount' => $contactData['amount'],
                    'description' => $validated['description'] . ' - ' . $contact->name,
                    'date' => $date,
                ]);
            }

            return $group->fresh();
        });
    }

    /**
     * Destroy a settlement group and its related data.
     */
    public function destroyGroup(SettlementGroup $group): void
    {
        DB::transaction(function () use ($group) {
            if ($group->financialTransaction) {
                $group->financialTransaction->items()->delete();
                $group->financialTransaction()->forceDelete();
            }

            // Soft-delete group (cascade will soft-delete children)
            $group->settlements()->delete();
            $group->delete();
        });
    }

    /**
     * Create the financial transaction with items for a split bill.
     */
    private function createTransaction(array $validated, Carbon $date): FinancialTransaction
    {
        $data = [
            'type' => 'expense',
            'amount' => $validated['total_amount'],
            'description' => $validated['description'] . ' (Conta Dividida)',
            'date' => $date,
            'is_posted' => true,
            'financial_account_id' => null,
            'financial_credit_card_invoice_id' => null,
        ];

        if ($validated['targetType'] === 'card') {
            $card = FinancialCreditCard::findOrFail($validated['financial_credit_card_id']);
            $invoice = FinancialCreditCardInvoice::resolveForDate($card, $date);
            $data['financial_credit_card_invoice_id'] = $invoice->id;
        } else {
            $data['financial_account_id'] = $validated['financial_account_id'];
        }

        $transaction = FinancialTransaction::create($data);

        // Item: "Minha Parte" (user's share, tagged with user-selected tags)
        $myItem = FinancialTransactionItem::create([
            'financial_transaction_id' => $transaction->id,
            'description' => 'Minha Parte',
            'quantity' => 1,
            'unit_price' => $validated['my_amount'],
            'total' => $validated['my_amount'],
        ]);

        // Attach user-selected tags to "Minha Parte"
        if (!empty($validated['tags'])) {
            $tagSync = [];
            foreach ($validated['tags'] as $tagId) {
                $isPrimary = !empty($validated['primary_tag_id']) 
                    ? (int)$tagId === (int)$validated['primary_tag_id'] 
                    : false;
                $tagSync[$tagId] = ['is_primary' => $isPrimary];
            }
            $myItem->tags()->sync($tagSync);
        }

        // Items: one per contact (tagged with Reembolso)
        foreach ($validated['contacts'] as $contactData) {
            $contact = Contact::findOrFail($contactData['id']);

            $contactItem = FinancialTransactionItem::create([
                'financial_transaction_id' => $transaction->id,
                'description' => $contact->name,
                'quantity' => 1,
                'unit_price' => $contactData['amount'],
                'total' => $contactData['amount'],
            ]);

            $contactItem->tags()->attach(FinancialTag::REEMBOLSO_ID, ['is_primary' => true]);
        }

        // Tag the transaction itself with all selected tags + Reembolso (none primary)
        $transactionTags = [];
        if (!empty($validated['tags'])) {
            foreach ($validated['tags'] as $tagId) {
                $transactionTags[$tagId] = ['is_primary' => false];
            }
        }
        $transactionTags[FinancialTag::REEMBOLSO_ID] = ['is_primary' => false];

        $transaction->tags()->sync($transactionTags);

        return $transaction;
    }
}

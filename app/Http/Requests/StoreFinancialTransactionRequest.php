<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFinancialTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'mode' => ['required', 'string', 'in:single,installment'],
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'date' => ['required', 'date'],
            'type' => ['required', 'string', 'in:income,expense'],
            'financial_account_id' => ['nullable', 'required_without:financial_credit_card_id', 'exists:financial_accounts,id'],
            'financial_credit_card_id' => ['nullable', 'required_without:financial_account_id', 'exists:financial_credit_cards,id'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['exists:financial_tags,id'],
            'installments' => ['required_if:mode,installment', 'integer', 'min:2'],
        ];
    }
}

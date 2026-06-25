<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFinancialTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        if ($this->has('amount') && is_string($this->amount)) {
            $amount = str_replace(['R$', ' '], '', $this->amount);
            if (strpos($amount, ',') !== false) {
                $amount = str_replace('.', '', $amount);
                $amount = str_replace(',', '.', $amount);
            }
            $this->merge(['amount' => $amount]);
        }
    }

    public function rules(): array
    {
        return [
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'date' => ['required', 'date'],
            'type' => ['required', 'string', 'in:income,expense'],
            'financial_account_id' => ['nullable', 'required_without:financial_credit_card_invoice_id', 'exists:financial_accounts,id'],
            'financial_credit_card_invoice_id' => ['nullable', 'required_without:financial_account_id', 'exists:financial_credit_card_invoices,id'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['exists:financial_tags,id'],
            'is_posted' => ['boolean'],
        ];
    }
}

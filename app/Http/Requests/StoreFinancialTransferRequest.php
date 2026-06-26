<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFinancialTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        foreach (['amount', 'fee_amount'] as $field) {
            if ($this->has($field) && is_string($this->$field)) {
                $this->merge([$field => str_replace(',', '.', $this->$field)]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'from_account_id' => ['required', 'exists:financial_accounts,id', 'different:to_account_id'],
            'to_account_id' => ['required', 'exists:financial_accounts,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'date' => ['required', 'date'],
            'description' => ['required', 'string', 'max:255'],
            'fee_amount' => ['nullable', 'numeric', 'min:0.01'],
            'fee_tag_id' => ['nullable', 'exists:financial_tags,id'],
        ];
    }
}

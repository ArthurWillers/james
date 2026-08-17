<?php

namespace App\Http\Requests;

use App\Enums\SettlementType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSettlementRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('amount') && is_string($this->amount)) {
            $this->merge(['amount' => str_replace(',', '.', $this->amount)]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', 'string', Rule::enum(SettlementType::class)],
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'date' => ['required', 'date'],
            'create_transaction' => ['boolean'],
            'targetType' => ['exclude_if:create_transaction,0', 'exclude_if:create_transaction,false', 'required', 'in:account,card'],
            'financial_account_id' => ['exclude_if:create_transaction,0', 'exclude_if:create_transaction,false', 'nullable', 'required_if:targetType,account', 'exists:financial_accounts,id'],
            'financial_credit_card_id' => ['exclude_if:create_transaction,0', 'exclude_if:create_transaction,false', 'nullable', 'required_if:targetType,card', 'exists:financial_credit_cards,id'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'mimes:jpeg,png,jpg,pdf', 'max:10240'], // 10MB max per file
        ];
    }

    public function messages(): array
    {
        return [
            'description.required' => 'A descrição é obrigatória.',
            'amount.required' => 'O valor é obrigatório.',
            'amount.min' => 'O valor deve ser maior que zero.',
            'date.required' => 'A data é obrigatória.',
            'financial_account_id.required_if' => 'Selecione uma conta bancária para a transação financeira.',
            'financial_credit_card_id.required_if' => 'Selecione um cartão de crédito para a transação financeira.',
            'attachments.*.mimes' => 'Os anexos devem ser imagens (JPEG, PNG) ou PDFs.',
            'attachments.*.max' => 'Cada anexo não pode ultrapassar 10MB.',
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSettlementGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // Convert comma-formatted amounts to dot notation
        if ($this->has('total_amount') && is_string($this->total_amount)) {
            $this->merge(['total_amount' => str_replace(',', '.', $this->total_amount)]);
        }

        if ($this->has('my_amount') && is_string($this->my_amount)) {
            $this->merge(['my_amount' => str_replace(',', '.', $this->my_amount)]);
        }

        if ($this->has('contacts') && is_array($this->contacts)) {
            $contacts = $this->contacts;
            foreach ($contacts as &$contact) {
                if (isset($contact['amount']) && is_string($contact['amount'])) {
                    $contact['amount'] = str_replace(',', '.', $contact['amount']);
                }
            }
            $this->merge(['contacts' => $contacts]);
        }
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'description' => ['required', 'string', 'max:255'],
            'total_amount' => ['required', 'numeric', 'min:0.01'],
            'my_amount' => ['required', 'numeric', 'min:0'],
            'date' => ['required', 'date'],
            'mode' => ['required', 'in:equal,exact'],
            'contacts' => ['required', 'array', 'min:1'],
            'contacts.*.id' => ['required', 'exists:contacts,id'],
            'contacts.*.amount' => ['required', 'numeric', 'min:0.01'],
            'create_transaction' => ['boolean'],
            'targetType' => ['exclude_if:create_transaction,0', 'exclude_if:create_transaction,false', 'required', 'in:account,card'],
            'financial_account_id' => ['exclude_if:create_transaction,0', 'exclude_if:create_transaction,false', 'nullable', 'required_if:targetType,account', 'exists:financial_accounts,id'],
            'financial_credit_card_id' => ['exclude_if:create_transaction,0', 'exclude_if:create_transaction,false', 'nullable', 'required_if:targetType,card', 'exists:financial_credit_cards,id'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['exists:financial_tags,id'],
            'primary_tag_id' => ['nullable', 'exists:financial_tags,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'description.required' => 'A descrição é obrigatória.',
            'total_amount.required' => 'O valor total é obrigatório.',
            'total_amount.min' => 'O valor total deve ser maior que zero.',
            'date.required' => 'A data é obrigatória.',
            'contacts.required' => 'Selecione ao menos um contato.',
            'contacts.min' => 'Selecione ao menos um contato.',
            'contacts.*.amount.required' => 'O valor de cada participante é obrigatório.',
            'contacts.*.amount.min' => 'O valor de cada participante deve ser maior que zero.',
            'financial_account_id.required_if' => 'Selecione uma conta bancária.',
            'financial_credit_card_id.required_if' => 'Selecione um cartão de crédito.',
        ];
    }
}

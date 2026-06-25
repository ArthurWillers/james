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
            $this->merge(['amount' => str_replace(',', '.', $this->amount)]);
        }

        if ($this->has('items') && is_array($this->items)) {
            $items = $this->items;
            foreach ($items as $key => $item) {
                if (isset($item['unit_price']) && is_string($item['unit_price'])) {
                    $items[$key]['unit_price'] = str_replace(',', '.', $item['unit_price']);
                }
                if (isset($item['quantity']) && is_string($item['quantity'])) {
                    $items[$key]['quantity'] = str_replace(',', '.', $item['quantity']);
                }
            }
            $this->merge(['items' => $items]);
        }
    }

    public function rules(): array
    {
        return [
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'date' => ['required', 'date'],
            'type' => ['required', 'string', 'in:income,expense'],
            'targetType' => ['required', 'string', 'in:account,card'],
            'financial_account_id' => ['nullable', 'required_if:targetType,account', 'exists:financial_accounts,id'],
            'financial_credit_card_invoice_id' => ['nullable', 'required_if:targetType,card', 'exists:financial_credit_card_invoices,id'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['exists:financial_tags,id'],
            'is_posted' => ['boolean'],
            'items' => ['nullable', 'array'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'not_in:0'],
            'items.*.unit_price' => ['required', 'numeric', 'not_in:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'description.required' => 'A descrição da transação é obrigatória.',
            'amount.required' => 'O valor da transação é obrigatório.',
            'amount.min' => 'O valor da transação deve ser maior que zero.',
            'financial_account_id.required_if' => 'Selecione uma conta bancária para esta transação.',
            'financial_credit_card_invoice_id.required_if' => 'Selecione uma fatura de cartão de crédito para esta transação.',
            'items.*.description.required' => 'Preencha a descrição de todos os itens adicionados.',
            'items.*.quantity.required' => 'A quantidade é obrigatória nos itens.',
            'items.*.quantity.not_in' => 'A quantidade do item não pode ser zero.',
            'items.*.unit_price.required' => 'O valor é obrigatório nos itens.',
            'items.*.unit_price.not_in' => 'O valor do item não pode ser zero.',
        ];
    }
}

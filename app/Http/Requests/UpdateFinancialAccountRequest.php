<?php

namespace App\Http\Requests;

use App\Enums\FinancialAccountType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFinancialAccountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::enum(FinancialAccountType::class)],
            'pix_keys' => ['nullable', 'array', 'prohibited_unless:type,checking'],
            'pix_keys.*.label' => ['required', 'string', 'max:255'],
            'pix_keys.*.value' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'pix_keys.*.label.required' => 'O rótulo da chave é obrigatório.',
            'pix_keys.*.value.required' => 'O valor da chave é obrigatório.',
        ];
    }
}

<?php

namespace App\Http\Requests;

use App\Rules\ValidIcon;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreFinancialTagRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255', 'unique:financial_tags,name'],
            'icon' => ['required', 'string', 'max:255', new ValidIcon],
            'color_hex' => ['nullable', 'string', 'regex:/^#([a-f0-9]{6}|[a-f0-9]{3})$/i'],
        ];
    }
}

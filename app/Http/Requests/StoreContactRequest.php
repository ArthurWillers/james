<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreContactRequest extends FormRequest
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
            'relationship_category' => ['nullable', 'string', 'max:255'],
            'birthdate' => ['nullable', 'date'],
            'phones' => ['nullable', 'array'],
            'pix_keys' => ['nullable', 'array'],
            'addresses' => ['nullable', 'array'],
            'notes' => ['nullable', 'string'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp,heic,heif', 'max:10240'],
        ];
    }
}

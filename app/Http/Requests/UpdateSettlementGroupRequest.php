<?php

namespace App\Http\Requests;

class UpdateSettlementGroupRequest extends StoreSettlementGroupRequest
{
    public function rules(): array
    {
        $rules = parent::rules();

        $rules['delete_attachments'] = ['nullable', 'array'];
        $rules['delete_attachments.*'] = ['integer', 'exists:media,id'];

        return $rules;
    }
}

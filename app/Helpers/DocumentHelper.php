<?php

namespace App\Helpers {
    class DocumentHelper
    {
        public static function format(string|int|null $value): string
        {
            $digits = preg_replace('/\D/', '', (string) $value) ?? '';

            return match (strlen($digits)) {
                11 => preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $digits),
                14 => preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $digits),
                default => (string) $value,
            };
        }
    }
}

namespace {
    use App\Helpers\DocumentHelper;

    if (! function_exists('formatCnpjCpf')) {
        function formatCnpjCpf(string|int|null $value): string
        {
            return DocumentHelper::format($value);
        }
    }
}

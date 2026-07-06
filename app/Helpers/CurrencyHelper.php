<?php

namespace App\Helpers {
    use Illuminate\Support\Number;

    class CurrencyHelper
    {
        /**
         * Formats a given numeric value into a currency string.
         */
        public static function format(int|float $value, string $currency = '', ?string $locale = null): string
        {
            return Number::currency($value, in: $currency, locale: $locale);
        }
    }
}

namespace {
    use App\Helpers\CurrencyHelper;

    if (! function_exists('formatCurrency')) {
        /**
         * Helper global para formatar moedas.
         */
        function formatCurrency(int|float $value, string $currency = '', ?string $locale = null): string
        {
            return CurrencyHelper::format($value, $currency, $locale);
        }
    }
}

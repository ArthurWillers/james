<?php

namespace App\Rules;

use BladeUI\Icons\Factory;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class ValidIcon implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail('O campo :attribute deve ser um texto.');

            return;
        }

        try {
            $svg = app(Factory::class)->svg($value);

            if ($svg === null) {
                $fail("O ícone '{$value}' não foi encontrado nas bibliotecas instaladas.");
            }
        } catch (\Exception $e) {
            $fail("O ícone '{$value}' não foi encontrado ou o prefixo é inválido.");
        }
    }
}

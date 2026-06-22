<?php

namespace App\Enums;

enum FinancialAccountType: string
{
    case Checking = 'checking';
    case Investment = 'investment';
    case Wallet = 'wallet';

    public function label(): string
    {
        return match ($this) {
            self::Checking => 'Conta Corrente',
            self::Investment => 'Investimentos',
            self::Wallet => 'Carteira',
        };
    }
}

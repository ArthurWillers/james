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

    public function icon(): string
    {
        return match ($this) {
            self::Checking => 'building-library',
            self::Investment => 'chart-bar',
            self::Wallet => 'wallet',
        };
    }
}

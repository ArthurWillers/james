<?php

namespace App\Enums;

enum SettlementType: string
{
    case TheyOwe = 'they_owe';
    case IOwe = 'i_owe';
    case TheyPaid = 'they_paid';
    case IPaid = 'i_paid';

    public function label(): string
    {
        return match ($this) {
            self::TheyOwe => 'Me deve',
            self::IOwe => 'Eu devo',
            self::TheyPaid => 'Recebi pgto.',
            self::IPaid => 'Realizei pgto.',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::TheyOwe => 'heroicon-m-arrow-trending-up',
            self::IOwe => 'heroicon-m-arrow-trending-down',
            self::TheyPaid => 'heroicon-m-arrow-down-left',
            self::IPaid => 'heroicon-m-arrow-up-right',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::TheyOwe, self::IPaid => 'green',
            self::IOwe, self::TheyPaid => 'red',
        };
    }
}

<?php

namespace App\Enums;

enum SettlementType: string
{
    case TheyOwe = 'they_owe';
    case IOwe = 'i_owe';
    case TheyPaid = 'they_paid';
    case IPaid = 'i_paid';
}

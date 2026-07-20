<?php

namespace App\Enums;

enum InvoiceStatus: string
{
    case Paid = 'paid';
    case PartiallyPaid = 'partially_paid';
    case Open = 'open';
    case Overdue = 'overdue';
    case Closed = 'closed';

    public function color(): string
    {
        return match ($this) {
            self::Paid => 'green',
            self::PartiallyPaid => 'yellow',
            self::Open => 'blue',
            self::Overdue => 'red',
            self::Closed => 'neutral',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Paid => 'Paga',
            self::PartiallyPaid => 'Parcialmente Paga',
            self::Open => 'Aberta',
            self::Overdue => 'Atrasada',
            self::Closed => 'Fechada',
        };
    }
}

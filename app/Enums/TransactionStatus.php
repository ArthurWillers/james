<?php

namespace App\Enums;

enum TransactionStatus: string
{
    case Draft = 'draft';
    case Pending = 'pending';
    case Posted = 'posted';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Rascunho',
            self::Pending => 'Pendente',
            self::Posted => 'Efetivada',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'neutral',
            self::Pending => 'warning',
            self::Posted => 'accent',
        };
    }
}

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

    /**
     * Returns a Tailwind color token (without bg- prefix) for badge use.
     */
    public function color(): string
    {
        return match ($this) {
            self::Draft => 'violet',
            self::Pending => 'amber',
            self::Posted => 'emerald',
        };
    }

    /**
     * Returns full Tailwind classes for a status badge.
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::Draft => 'bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-400',
            self::Pending => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
            self::Posted => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
        };
    }

    public function isPosted(): bool
    {
        return $this === self::Posted;
    }
}

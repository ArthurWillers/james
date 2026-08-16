<?php

namespace App\Enums;

enum NotificationLevel: string
{
    case Info = 'info';
    case Success = 'success';
    case Warning = 'warning';
    case Danger = 'danger';

    public function label(): string
    {
        return match ($this) {
            self::Info => 'Informativo',
            self::Success => 'Sucesso',
            self::Warning => 'Alerta',
            self::Danger => 'Atenção',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Info => 'blue',
            self::Success => 'green',
            self::Warning => 'yellow',
            self::Danger => 'red',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Info => 'heroicon-o-information-circle',
            self::Success => 'heroicon-o-check-circle',
            self::Warning => 'heroicon-o-exclamation-triangle',
            self::Danger => 'heroicon-o-exclamation-circle',
        };
    }
}

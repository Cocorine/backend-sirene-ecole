<?php

namespace App\Enums;

enum TypeNotification: string
{
    case SMS = 'SMS';
    case EMAIL = 'EMAIL';
    case SYSTEME = 'SYSTEME';

    public function label(): string
    {
        return match($this) {
            self::SMS => 'SMS',
            self::EMAIL => 'Email',
            self::SYSTEME => 'Système',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
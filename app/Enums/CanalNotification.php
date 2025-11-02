<?php

namespace App\Enums;

enum CanalNotification: string
{
    case SMS = 'sms';
    case EMAIL = 'email';
    case PUSH = 'push';
    case SYSTEME = 'systeme';

    public function label(): string
    {
        return match($this) {
            self::SMS => 'SMS',
            self::EMAIL => 'Email',
            self::PUSH => 'Push',
            self::SYSTEME => 'Système',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
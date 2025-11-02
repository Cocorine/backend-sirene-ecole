<?php

namespace App\Enums;

enum StatutEcole: string
{
    case ACTIF = 'actif';
    case INACTIF = 'inactif';
    case SUSPENDU = 'suspendu';

    public function label(): string
    {
        return match($this) {
            self::ACTIF => 'Actif',
            self::INACTIF => 'Inactif',
            self::SUSPENDU => 'Suspendu',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
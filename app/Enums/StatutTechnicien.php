<?php

namespace App\Enums;

enum StatutTechnicien: string
{
    case ACTIF = 'actif';
    case INACTIF = 'inactif';
    case EN_MISSION = 'en_mission';

    public function label(): string
    {
        return match($this) {
            self::ACTIF => 'Actif',
            self::INACTIF => 'Inactif',
            self::EN_MISSION => 'En mission',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
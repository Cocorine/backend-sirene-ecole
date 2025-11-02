<?php

namespace App\Enums;

enum StatutSirene: string
{
    case EN_STOCK = 'en_stock';
    case INSTALLE = 'installe';
    case EN_PANNE = 'en_panne';
    case HORS_SERVICE = 'hors_service';

    public function label(): string
    {
        return match($this) {
            self::EN_STOCK => 'En Stock',
            self::INSTALLE => 'Installé',
            self::EN_PANNE => 'En Panne',
            self::HORS_SERVICE => 'Hors Service',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

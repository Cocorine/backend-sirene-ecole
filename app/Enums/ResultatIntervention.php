<?php

namespace App\Enums;

enum ResultatIntervention: string
{
    case RESOLU = 'resolu';
    case PARTIELLEMENT_RESOLU = 'partiellement_resolu';
    case NON_RESOLU = 'non_resolu';

    public function label(): string
    {
        return match($this) {
            self::RESOLU => 'Résolu',
            self::PARTIELLEMENT_RESOLU => 'Partiellement résolu',
            self::NON_RESOLU => 'Non résolu',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
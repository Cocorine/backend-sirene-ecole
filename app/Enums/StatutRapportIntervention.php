<?php

namespace App\Enums;

enum StatutRapportIntervention: string
{
    case BROUILLON = 'brouillon';
    case VALIDE = 'valide';

    public function label(): string
    {
        return match($this) {
            self::BROUILLON => 'Brouillon',
            self::VALIDE => 'Validé',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
<?php

namespace App\Enums;

enum StatutPaiement: string
{
    case EN_ATTENTE = 'en_attente';
    case VALIDE = 'valide';
    case ECHOUE = 'echoue';
    case REMBOURSE = 'rembourse';

    public function label(): string
    {
        return match($this) {
            self::EN_ATTENTE => 'En attente',
            self::VALIDE => 'Validé',
            self::ECHOUE => 'Échoué',
            self::REMBOURSE => 'Remboursé',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

<?php

namespace App\Enums;

enum StatutPanne: string
{
    case EN_ATTENTE = 'EN_ATTENTE';
    case OUVERTE = 'OUVERTE';
    case EN_COURS = 'EN_COURS';
    case RESOLUE = 'RESOLUE';
    case CLOTUREE = 'CLOTUREE';

    public function label(): string
    {
        return match($this) {
            self::EN_ATTENTE => 'En attente',
            self::OUVERTE => 'Ouverte',
            self::EN_COURS => 'En cours',
            self::RESOLUE => 'Résolue',
            self::CLOTUREE => 'Clôturée',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
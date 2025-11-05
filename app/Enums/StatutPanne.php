<?php

namespace App\Enums;

enum StatutPanne: string
{
    case DECLAREE = 'declaree';
    case VALIDEE = 'validee';
    case ASSIGNEE = 'assignee';
    case EN_COURS = 'en_cours';
    case RESOLUE = 'resolue';
    case CLOTUREE = 'cloturee';

    public function label(): string
    {
        return match($this) {
            self::DECLAREE => 'Déclarée',
            self::VALIDEE => 'Validée',
            self::ASSIGNEE => 'Assignée',
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

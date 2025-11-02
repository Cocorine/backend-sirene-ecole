<?php

namespace App\Enums;

enum TypeUtilisateur: string
{
    case ADMIN = 'ADMIN';
    case TECHNICIEN = 'TECHNICIEN';
    case ECOLE = 'ECOLE';

    public function label(): string
    {
        return match($this) {
            self::ADMIN => 'Administrateur',
            self::TECHNICIEN => 'Technicien',
            self::ECOLE => 'École',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
<?php

namespace App\Enums;

enum TypeEtablissement: string
{
    case MATERNELLE = 'MATERNELLE';
    case PRIMAIRE = 'PRIMAIRE';
    case SECONDAIRE = 'SECONDAIRE';
    case SUPERIEUR = 'SUPERIEUR';

    public function label(): string
    {
        return match($this) {
            self::MATERNELLE => 'Maternelle',
            self::PRIMAIRE => 'Primaire',
            self::SECONDAIRE => 'Secondaire',
            self::SUPERIEUR => 'Supérieur',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
<?php

namespace App\Enums;

enum PrioritePanne: string
{
    case BASSE = 'basse';
    case MOYENNE = 'moyenne';
    case HAUTE = 'haute';
    case URGENTE = 'urgente';

    public function label(): string
    {
        return match($this) {
            self::BASSE => 'Basse',
            self::MOYENNE => 'Moyenne',
            self::HAUTE => 'Haute',
            self::URGENTE => 'Urgente',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
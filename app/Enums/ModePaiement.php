<?php

namespace App\Enums;

enum ModePaiement: string
{
    case QR_CODE = 'qr_code';
    case EN_LIGNE = 'en_ligne';
    case MOBILE_MONEY = 'mobile_money';
    case CARTE = 'carte';
    case ESPECES = 'especes';

    public function label(): string
    {
        return match($this) {
            self::QR_CODE => 'QR Code',
            self::EN_LIGNE => 'En Ligne',
            self::MOBILE_MONEY => 'Mobile Money',
            self::CARTE => 'Carte Bancaire',
            self::ESPECES => 'Espèces',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

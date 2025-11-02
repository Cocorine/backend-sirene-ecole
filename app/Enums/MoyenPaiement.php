<?php

namespace App\Enums;

enum MoyenPaiement: string
{
    case MOBILE_MONEY = 'MOBILE_MONEY';
    case CARTE_BANCAIRE = 'CARTE_BANCAIRE';
    case QR_CODE = 'QR_CODE';
    case VIREMENT = 'VIREMENT';

    public function label(): string
    {
        return match($this) {
            self::MOBILE_MONEY => 'Mobile Money',
            self::CARTE_BANCAIRE => 'Carte Bancaire',
            self::QR_CODE => 'QR Code',
            self::VIREMENT => 'Virement Bancaire',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
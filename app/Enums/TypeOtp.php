<?php

namespace App\Enums;

enum TypeOtp: string
{
    case LOGIN = 'login';
    case PASSWORD_RESET = 'password_reset';
    case VERIFICATION = 'verification';
    case TWO_FACTOR = 'two_factor';

    public function label(): string
    {
        return match($this) {
            self::LOGIN => 'Connexion',
            self::PASSWORD_RESET => 'Réinitialisation de mot de passe',
            self::VERIFICATION => 'Vérification',
            self::TWO_FACTOR => 'Authentification à deux facteurs',
        };
    }

    public function expiration(): int
    {
        return match($this) {
            self::LOGIN => 5,           // 5 minutes
            self::PASSWORD_RESET => 10, // 10 minutes
            self::VERIFICATION => 15,   // 15 minutes
            self::TWO_FACTOR => 3,      // 3 minutes
        };
    }

    public function maxAttempts(): int
    {
        return match($this) {
            self::LOGIN => 5,
            self::PASSWORD_RESET => 3,
            self::VERIFICATION => 5,
            self::TWO_FACTOR => 3,
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

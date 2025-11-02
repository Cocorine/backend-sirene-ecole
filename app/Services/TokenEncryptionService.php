<?php

namespace App\Services;

use Exception;

class TokenEncryptionService
{
    private string $encryptionKey;
    private string $cipher;

    public function __construct()
    {
        $this->encryptionKey = config('app.token_encryption_key', env('TOKEN_ENCRYPTION_KEY'));
        $this->cipher = config('app.token_encryption_method', 'AES-256-CBC');
    }

    /**
     * Crypter un token avec les informations de l'abonnement
     */
    public function encryptToken(array $data): string
    {
        $jsonData = json_encode($data);
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($this->cipher));

        $encrypted = openssl_encrypt(
            $jsonData,
            $this->cipher,
            base64_decode($this->encryptionKey),
            0,
            $iv
        );

        if ($encrypted === false) {
            throw new Exception('Échec du cryptage du token');
        }

        return base64_encode($iv . $encrypted);
    }

    /**
     * Décrypter un token
     */
    public function decryptToken(string $encryptedToken): array
    {
        $data = base64_decode($encryptedToken);
        $ivLength = openssl_cipher_iv_length($this->cipher);

        $iv = substr($data, 0, $ivLength);
        $encrypted = substr($data, $ivLength);

        $decrypted = openssl_decrypt(
            $encrypted,
            $this->cipher,
            base64_decode($this->encryptionKey),
            0,
            $iv
        );

        if ($decrypted === false) {
            throw new Exception('Échec du décryptage du token');
        }

        return json_decode($decrypted, true);
    }

    /**
     * Générer un hash du token
     */
    public function hashToken(string $token): string
    {
        return hash('sha256', $token);
    }

    /**
     * Générer une clé de cryptage
     */
    public static function generateEncryptionKey(): string
    {
        return base64_encode(openssl_random_pseudo_bytes(32));
    }
}

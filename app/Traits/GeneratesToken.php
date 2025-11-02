<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Crypt;
use App\Models\TokenSirene;
use Carbon\Carbon;

trait GeneratesToken
{
    /**
     * Boot the trait and automatically generate token on Sirene creation
     */
    protected static function bootGeneratesToken(): void
    {
        static::created(function (Model $model) {
            if (isset($model->numero_serie) && !$model->tokenActif) {
                self::createSireneToken($model);
            }
        });
    }

    /**
     * Generate and create token for the siren
     */
    protected static function createSireneToken(Model $model): TokenSirene
    {
        $token = self::generateSecureToken();
        $encryptedToken = self::encryptToken($token);
        $hashedToken = hash('sha256', $token);

        return TokenSirene::create([
            'sirene_id' => $model->id,
            'token' => $encryptedToken,
            'token_hash' => $hashedToken,
            'date_generation' => Carbon::now(),
            'date_expiration' => Carbon::now()->addYear(),
            'est_actif' => true,
        ]);
    }

    /**
     * Generate a secure random token
     */
    protected static function generateSecureToken(): string
    {
        return Str::random(64) . '_' . time() . '_' . Str::random(16);
    }

    /**
     * Encrypt token using AES-256-CBC
     */
    protected static function encryptToken(string $token): string
    {
        return Crypt::encryptString($token);
    }

    /**
     * Decrypt token
     */
    public function decryptToken(string $encryptedToken): string
    {
        return Crypt::decryptString($encryptedToken);
    }

    /**
     * Get active token
     */
    public function tokenActif()
    {
        return $this->hasOne(TokenSirene::class, 'sirene_id')
            ->where('est_actif', true)
            ->where('date_expiration', '>=', Carbon::now())
            ->latest('date_generation');
    }

    /**
     * Get all tokens
     */
    public function tokens()
    {
        return $this->hasMany(TokenSirene::class, 'sirene_id')->latest('date_generation');
    }

    /**
     * Regenerate token (deactivate old one and create new)
     */
    public function regenerateToken(): TokenSirene
    {
        // Deactivate old token
        $oldToken = $this->tokenActif;
        if ($oldToken) {
            $oldToken->update(['est_actif' => false]);
        }

        // Create new token
        return self::createSireneToken($this);
    }

    /**
     * Verify token hash
     */
    public function verifyToken(string $plainToken): bool
    {
        $tokenActif = $this->tokenActif;
        if (!$tokenActif) {
            return false;
        }

        $hashedToken = hash('sha256', $plainToken);
        return hash_equals($tokenActif->token_hash, $hashedToken);
    }

    /**
     * Get decrypted active token
     */
    public function getDecryptedToken(): ?string
    {
        $tokenActif = $this->tokenActif;
        if (!$tokenActif) {
            return null;
        }

        return $this->decryptToken($tokenActif->token);
    }
}

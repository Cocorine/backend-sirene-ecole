<?php

namespace App\Models;

use App\Traits\HasUlid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TokenSirene extends Model
{
    use HasUlid, SoftDeletes;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $table = 'tokens_sirene';

    protected $fillable = [
        'abonnement_id',
        'sirene_id',
        'site_id',
        'token_crypte',
        'token_hash',
        'date_debut',
        'date_fin',
        'actif',
        'date_generation',
        'date_expiration',
        'date_activation',
        'genere_par',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
        'actif' => 'boolean',
        'date_generation' => 'datetime',
        'date_expiration' => 'datetime',
        'date_activation' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relations
    public function abonnement(): BelongsTo
    {
        return $this->belongsTo(Abonnement::class, 'abonnement_id');
    }

    public function sirene(): BelongsTo
    {
        return $this->belongsTo(Sirene::class, 'sirene_id');
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'site_id');
    }

    // Helpers
    public function isValid(): bool
    {
        return $this->actif
            && $this->date_debut <= now()
            && $this->date_fin >= now();
    }

    /**
     * Décrypte le token et retourne les données
     */
    public function decrypterToken(): ?array
    {
        if (!$this->token_crypte) {
            return null;
        }

        try {
            $decrypted = \Illuminate\Support\Facades\Crypt::decryptString($this->token_crypte);
            return json_decode($decrypted, true);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erreur décryptage token: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Vérifie l'intégrité du token
     */
    public function verifierToken(): bool
    {
        $tokenData = $this->decrypterToken();

        if (!$tokenData) {
            return false;
        }

        // Vérifier l'expiration
        if (isset($tokenData['expires_at'])) {
            $expiresAt = \Carbon\Carbon::parse($tokenData['expires_at']);
            if ($expiresAt->isPast()) {
                return false;
            }
        }

        // Vérifier la correspondance des données
        return $tokenData['sirene_id'] === $this->sirene_id
            && $tokenData['abonnement_id'] === $this->abonnement_id;
    }

    /**
     * Formatte le token pour affichage (segments de 4 caractères)
     */
    public function getTokenFormatted(): ?string
    {
        if (!$this->token_crypte) {
            return null;
        }

        $token = base64_encode($this->token_crypte);
        return rtrim(chunk_split($token, 4, '-'), '-');
    }

    /**
     * Vérifie le hash du token
     */
    public function verifierHash(string $tokenCrypte): bool
    {
        return hash('sha256', $tokenCrypte) === $this->token_hash;
    }
}

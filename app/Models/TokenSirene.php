<?php

namespace App\Models;

use App\Traits\HasUlid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TokenSirene extends Model
{
    use HasUlid, SoftDeletes;

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
}

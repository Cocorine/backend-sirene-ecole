<?php

namespace App\Models;

use App\Enums\MoyenPaiement as MoyenPaiementEnum;
use App\Traits\HasUlid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MoyenPaiement extends Model
{
    use HasUlid, SoftDeletes;

    protected $table = 'moyens_paiement';

    protected $fillable = [
        'paiementable_id', 'paiementable_type', 'type', 'operateur',
        'numero_telephone', 'numero_carte_masque', 'token_carte',
        'nom_titulaire', 'date_expiration', 'email_wallet', 'id_wallet',
        'par_defaut', 'actif', 'derniere_utilisation'
    ];

    protected $casts = [
        'type' => MoyenPaiementEnum::class,
        'par_defaut' => 'boolean',
        'actif' => 'boolean',
        'derniere_utilisation' => 'datetime',
    ];

    public function paiementable(): MorphTo
    {
        return $this->morphTo();
    }
}

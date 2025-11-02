<?php

namespace App\Models;

use App\Enums\MoyenPaiement;
use App\Traits\HasUlid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Paiement extends Model
{
    use HasUlid, SoftDeletes;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $table = 'paiements';

    protected $fillable = [
        'abonnement_id',
        'ecole_id',
        'numero_transaction',
        'montant',
        'moyen',
        'statut',
        'reference_externe',
        'metadata',
        'date_paiement',
        'date_validation',
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'moyen' => MoyenPaiement::class,
        'metadata' => 'array',
        'date_paiement' => 'datetime',
        'date_validation' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];
}

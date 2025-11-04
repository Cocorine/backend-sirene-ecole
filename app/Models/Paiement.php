<?php

namespace App\Models;

use App\Enums\MoyenPaiement;
use App\Traits\HasUlid;
use App\Traits\SoftDeletesUniqueFields;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Paiement extends Model
{
    use HasUlid, SoftDeletes, SoftDeletesUniqueFields;

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

    /**
     * Get the unique fields that should be updated on soft delete.
     *
     * @return array
     */
    protected function getUniqueSoftDeleteFields(): array
    {
        return ['numero_transaction', 'reference_externe'];
    }

    public function abonnement(): BelongsTo
    {
        return $this->belongsTo(Abonnement::class, 'abonnement_id');
    }

    public function ecole(): BelongsTo
    {
        return $this->belongsTo(Ecole::class, 'ecole_id');
    }
}

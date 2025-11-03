<?php

namespace App\Models;

use App\Traits\HasUlid;
use App\Traits\SoftDeletesUniqueFields;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class JourFerie extends Model
{
    use HasUlid, SoftDeletes;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $table = 'jours_feries';

    protected $fillable = [
        'calendrier_id',
        'ecole_id',
        'pays_id',
        'libelle',
        'nom',
        'date_ferie',
        'date',
        'type',
        'recurrent',
        'actif',
    ];

    protected $casts = [
        'date_ferie' => 'date',
        'date' => 'date',
        'recurrent' => 'boolean',
        'actif' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the unique fields that should be updated on soft delete.
     * @return array
     */
    protected function getUniqueSoftDeleteFields(): array
    {
        return ['libelle', 'nom'];
    }
}

<?php

namespace App\Models;

use App\Traits\HasUlid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pays extends Model
{
    use HasUlid, SoftDeletes;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $table = 'pays';

    protected $fillable = [
        'nom',
        'code_iso',
        'indicatif_tel',
        'devise',
        'fuseau_horaire',
        'actif',
    ];

    protected $casts = [
        'actif' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relations
    public function villes(): HasMany
    {
        return $this->hasMany(Ville::class);
    }

    public function ecoles(): HasMany
    {
        return $this->hasMany(Ecole::class);
    }

    public function calendriersScolaires(): HasMany
    {
        return $this->hasMany(CalendrierScolaire::class);
    }

    public function joursFeries(): HasMany
    {
        return $this->hasMany(JourFerie::class);
    }
}

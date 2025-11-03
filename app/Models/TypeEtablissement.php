<?php

namespace App\Models;

use App\Traits\HasUlid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TypeEtablissement extends Model
{
    use HasUlid, SoftDeletes;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $table = 'types_etablissement';

    protected $fillable = [
        'type',
        'code',
        'description',
        'actif',
    ];

    protected $casts = [
        'actif' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function ecoles(): HasMany
    {
        return $this->hasMany(Ecole::class);
    }
}

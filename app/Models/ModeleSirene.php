<?php

namespace App\Models;

use App\Traits\HasUlid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ModeleSirene extends Model
{
    use HasUlid, SoftDeletes;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $table = 'modeles_sirene';

    protected $fillable = [
        'nom',
        'reference',
        'description',
        'specifications',
        'version_firmware',
        'prix_unitaire',
        'actif',
    ];

    protected $casts = [
        'specifications' => 'array',
        'prix_unitaire' => 'decimal:2',
        'actif' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function sirenes(): HasMany
    {
        return $this->hasMany(Sirene::class);
    }
}

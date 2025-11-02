<?php

namespace App\Models;

use App\Traits\HasUlid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ville extends Model
{
    use HasUlid, SoftDeletes;

    protected $table = 'villes';

    protected $fillable = [
        'pays_id',
        'nom',
        'code',
        'latitude',
        'longitude',
        'actif',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'actif' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relations
    public function pays(): BelongsTo
    {
        return $this->belongsTo(Pays::class, 'pays_id');
    }

    public function ecoles(): HasMany
    {
        return $this->hasMany(Ecole::class);
    }

    public function techniciens(): HasMany
    {
        return $this->hasMany(Technicien::class);
    }
}

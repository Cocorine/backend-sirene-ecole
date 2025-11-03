<?php

namespace App\Models;

use App\Enums\StatutTechnicien;
use App\Traits\HasUlid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Technicien extends Model
{
    use HasUlid, SoftDeletes;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $table = 'techniciens';

    protected $fillable = [
        'ville_id',
        'review',
        'specialite',
        'disponibilite',
        'date_inscription',
        'statut',
        'date_embauche',
    ];

    protected $casts = [
        'review' => 'decimal:1',
        'disponibilite' => 'boolean',
        'date_inscription' => 'datetime',
        'statut' => StatutTechnicien::class,
        'date_embauche' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relations
    public function ville(): BelongsTo
    {
        return $this->belongsTo(Ville::class, 'ville_id');
    }

    public function interventions(): HasMany
    {
        return $this->hasMany(Intervention::class);
    }

    public function notifications(): MorphMany
    {
        return $this->morphMany(Notification::class, 'notifiable');
    }
}

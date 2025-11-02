<?php

namespace App\Models;

use App\Traits\HasUlid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrdreMission extends Model
{
    use HasUlid, SoftDeletes;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $table = 'ordres_mission';

    protected $fillable = [
        'panne_id',
        'ville_id',
        'date_generation',
        'technicien_id',
        'date_acceptation',
        'valide_par',
        'statut',
        'commentaire',
    ];

    protected $casts = [
        'date_generation' => 'datetime',
        'date_acceptation' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relations
    public function panne(): BelongsTo
    {
        return $this->belongsTo(Panne::class, 'panne_id');
    }

    public function ville(): BelongsTo
    {
        return $this->belongsTo(Ville::class, 'ville_id');
    }

    public function technicien(): BelongsTo
    {
        return $this->belongsTo(Technicien::class, 'technicien_id');
    }

    public function validePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'valide_par');
    }

    public function missionsTechniciens(): HasMany
    {
        return $this->hasMany(MissionTechnicien::class, 'ordre_mission_id');
    }

    public function interventions(): HasMany
    {
        return $this->hasMany(Intervention::class, 'ordre_mission_id');
    }
}

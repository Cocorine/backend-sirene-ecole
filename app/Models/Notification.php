<?php

namespace App\Models;

use App\Enums\CanalNotification; // Assuming this enum exists or will be created
use App\Enums\TypeNotification; // Assuming this enum exists or will be created
use App\Traits\HasUlid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notification extends Model
{
    use HasUlid, SoftDeletes;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $table = 'notifications';

    protected $fillable = [
        'notifiable_id',
        'notifiable_type',
        'ecole_id',
        'technicien_id',
        'type',
        'canal',
        'message',
        'titre',
        'data',
        'statut',
        'lu',
        'date_lecture',
        'date_envoi',
    ];

    protected $casts = [
        'type' => TypeNotification::class,
        'canal' => CanalNotification::class,
        'data' => 'array',
        'statut' => 'boolean',
        'lu' => 'boolean',
        'date_lecture' => 'datetime',
        'date_envoi' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relations
    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }

    public function ecole(): BelongsTo
    {
        return $this->belongsTo(Ecole::class, 'ecole_id');
    }

    public function technicien(): BelongsTo
    {
        return $this->belongsTo(Technicien::class, 'technicien_id');
    }
}

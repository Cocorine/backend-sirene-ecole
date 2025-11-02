<?php

namespace App\Models;

use App\Traits\HasUlid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class OtpCode extends Model
{
    use HasUlid, SoftDeletes;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $table = 'otp_codes';

    protected $fillable = [
        'user_id',
        'date_generation',
        'expire_le',
        'utilise',
        'telephone',
        'code',
        'valide',
        'est_verifie',
        'verifie',
        'date_expiration',
        'date_verification',
        'tentatives',
    ];

    protected $casts = [
        'date_generation' => 'datetime',
        'expire_le' => 'datetime',
        'utilise' => 'boolean',
        'valide' => 'boolean',
        'est_verifie' => 'boolean',
        'verifie' => 'boolean',
        'date_expiration' => 'datetime',
        'date_verification' => 'datetime',
        'tentatives' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relations
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

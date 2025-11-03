<?php

namespace App\Models;

use App\Traits\HasUlid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\SoftDeletesUniqueFields;

class UserInfo extends Model
{
    use HasFactory, HasUlid, SoftDeletes, SoftDeletesUniqueFields;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $table = 'user_infos';

    protected $fillable = [
        'user_id',
        'nom',
        'prenom',
        'telephone',
        'email',
        'ville_id',
        'adresse',
    ];

    /**
     * Get the unique fields that should be updated on soft delete.
     *
     * @return array
     */
    protected function getUniqueSoftDeleteFields(): array
    {
        return ['telephone', 'email'];
    }

    // User relationship
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Ville relationship
    public function ville(): BelongsTo
    {
        return $this->belongsTo(Ville::class, 'ville_id');
    }
}

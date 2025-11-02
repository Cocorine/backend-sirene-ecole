<?php

namespace App\Models;

use App\Traits\HasUlid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

class RolePermission extends Pivot
{
    use HasUlid, SoftDeletes;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $table = 'role_permissions';

    protected $fillable = [
        'role_id',
        'permission_id',
    ];

    // Role relationship
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    // Permission relationship
    public function permission(): BelongsTo
    {
        return $this->belongsTo(Permission::class, 'permission_id');
    }
}

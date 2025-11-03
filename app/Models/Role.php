<?php

namespace App\Models;

use App\Traits\HasUlid;
use App\Traits\HasSlug; // Added
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\SoftDeletesUniqueFields;

class Role extends Model
{
    use HasFactory, HasUlid, SoftDeletes, HasSlug, SoftDeletesUniqueFields; // Added HasSlug

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $table = 'roles';

    protected $fillable = [
        'nom',
        'slug',
        'roleable_id',
        'roleable_type',
    ];

    /**
     * Get the unique fields that should be updated on soft delete.
     *
     * @return array
     */
    protected function getUniqueSoftDeleteFields(): array
    {
        return ['nom', 'slug'];
    }

    // Polymorphic relationship
    public function roleable(): MorphTo
    {
        return $this->morphTo();
    }

    // Permissions relationship
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permissions', 'role_id', 'permission_id')->using(RolePermission::class)->withPivot([])
            ->withTimestamps();
    }

    // Users relationship (assuming a User can have one Role directly)
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'role_id');
    }
}

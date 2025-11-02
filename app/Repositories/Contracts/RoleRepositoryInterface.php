<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface RoleRepositoryInterface extends BaseRepositoryInterface
{
    public function findBySlug(string $slug, array $relations = []): ?Model;
    public function getRolesByRoleable(string $roleableId, string $roleableType, array $relations = []): Collection;
    public function findByRoleable(string $roleableId, string $roleableType);
    public function attachPermissions(string $roleId, array $permissionIds): void;
    public function detachPermissions(string $roleId, array $permissionIds): void;
    public function syncPermissions(string $roleId, array $permissionIds): void;
}

<?php

namespace App\Services\Contracts;

use Illuminate\Http\JsonResponse;

interface RoleServiceInterface extends BaseServiceInterface
{
    public function findBySlug(string $slug): JsonResponse;
    public function getRolesByRoleable(string $roleableId, string $roleableType): JsonResponse;
    public function createRole(array $data): JsonResponse;
    public function updateRole(string $id, array $data): JsonResponse;
    public function assignPermissionsToRole(string $roleId, array $permissionIds): JsonResponse;
    public function syncPermissionsToRole(string $roleId, array $permissionIds): JsonResponse;
    public function removePermissionsFromRole(string $roleId, array $permissionIds): JsonResponse;
}

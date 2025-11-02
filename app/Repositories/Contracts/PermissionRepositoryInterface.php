<?php

namespace App\Repositories\Contracts;

interface PermissionRepositoryInterface extends BaseRepositoryInterface
{
    public function findBySlug(string $slug);
    public function getByRole(string $roleId);
    public function findExistingPermissionIds(array $permissionIds): array;
}

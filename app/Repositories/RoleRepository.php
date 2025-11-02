<?php

namespace App\Repositories;

use App\Models\Role;
use App\Repositories\Contracts\PermissionRepositoryInterface; // Added
use App\Repositories\Contracts\RoleRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class RoleRepository extends BaseRepository implements RoleRepositoryInterface
{
    protected PermissionRepositoryInterface $permissionRepository; // Added

    public function __construct(Role $model, PermissionRepositoryInterface $permissionRepository) // Modified
    {
        parent::__construct($model);
        $this->permissionRepository = $permissionRepository; // Added
    }


    public function findBySlug(string $slug, array $relations = []): ?Model
    {
        return $this->model->with($relations)->where('slug', $slug)->first();
    }

    public function getRolesByRoleable(string $roleableId, string $roleableType, array $relations = []): Collection
    {
        return $this->model->with($relations)
            ->where('roleable_id', $roleableId)
            ->where('roleable_type', $roleableType)
            ->get();
    }

    public function findByRoleable(string $roleableId, string $roleableType)
    {
        return $this->model
            ->where('roleable_id', $roleableId)
            ->where('roleable_type', $roleableType)
            ->get();
    }

    public function createWithPermissions(array $data, array $permissionIds): Role
    {
        try {
            $role = $this->create($data);
            $validPermissionIds = $this->permissionRepository->findExistingPermissionIds($permissionIds); // Modified
            if (!empty($validPermissionIds)) {
                $role->permissions()->attach($validPermissionIds);
            }
            return $role;
        } catch (\Exception $e) {
            Log::error("Error creating role with permissions: " . $e->getMessage());
            throw $e;
        }
    }

    public function attachPermissions(string $roleId, array $permissionIds): void
    {
        try {
            $role = $this->find($roleId);
            if ($role) {
                $validPermissionIds = $this->permissionRepository->findExistingPermissionIds($permissionIds); // Modified
                if (!empty($validPermissionIds)) {
                    $role->permissions()->attach($validPermissionIds);
                }
            }
        } catch (\Exception $e) {
            Log::error("Error attaching permissions to role {$roleId}: " . $e->getMessage());
            throw $e;
        }
    }

    public function detachPermissions(string $roleId, array $permissionIds): void
    {
        try {
            $role = $this->find($roleId);
            if ($role) {
                $validPermissionIds = $this->permissionRepository->findExistingPermissionIds($permissionIds); // Modified
                if (!empty($validPermissionIds)) {
                    $role->permissions()->detach($validPermissionIds);
                }
            }
        } catch (\Exception $e) {
            Log::error("Error detaching permissions from role {$roleId}: " . $e->getMessage());
            throw $e;
        }
    }

    public function syncPermissions(string $roleId, array $permissionIds): void
    {
        try {
            $role = $this->find($roleId);
            if ($role) {
                $validPermissionIds = $this->permissionRepository->findExistingPermissionIds($permissionIds); // Modified
                $role->permissions()->sync($validPermissionIds);
            }
        } catch (\Exception $e) {
            Log::error("Error syncing permissions for role {$roleId}: " . $e->getMessage());
            throw $e;
        }
    }
}

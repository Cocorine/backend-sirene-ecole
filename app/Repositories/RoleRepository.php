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

    /**
     * Get all roles excluding default roles (roles without profilable)
     * Automatically filters by authenticated user's account type
     *
     * @param array $columns
     * @param array $relations
     * @param string|null $profilableId Filter by profilable_id (optional)
     * @param bool $excludeDefaults Exclude roles where roleable_id and roleable_type are NULL
     * @return Collection
     */
    public function all(
        array $columns = ['*'],
        array $relations = [],
        ?string $profilableId = null,
        bool $excludeDefaults = true
    ): Collection {
        $query = $this->model->with($relations);

        // Récupérer automatiquement le type de profil depuis l'utilisateur connecté
        $user = auth()->user();
        $profilableType = $user ? $user->user_account_type_type : null;

        // Exclure les rôles par défaut (rôles système sans profilable)
        if ($excludeDefaults) {
            $query->whereNotNull('roleable_id')
                  ->whereNotNull('roleable_type');
        }

        // Filtrer par profilable_type récupéré de l'utilisateur
        if ($profilableType !== null) {
            $query->where('roleable_type', $profilableType);
        }

        // Filtrer par profilable_id si fourni
        if ($profilableId !== null) {
            $query->where('roleable_id', $profilableId);
        }

        return $query->get($columns);
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

    /**
     * Paginate roles with filters
     * Automatically filters by authenticated user's account type
     *
     * @param int $perPage
     * @param array $columns
     * @param array $relations
     * @param string|null $profilableId Filter by profilable_id (optional)
     * @param bool $excludeDefaults Exclude default roles
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function paginate(
        int $perPage = 15,
        array $columns = ['*'],
        array $relations = [],
        ?string $profilableId = null,
        bool $excludeDefaults = true
    ): \Illuminate\Pagination\LengthAwarePaginator {
        $query = $this->model->with($relations);

        // Récupérer automatiquement le type de profil depuis l'utilisateur connecté
        $user = auth()->user();
        $profilableType = $user ? $user->user_account_type_type : null;

        // Exclure les rôles par défaut (rôles système sans profilable)
        if ($excludeDefaults) {
            $query->whereNotNull('roleable_id')
                  ->whereNotNull('roleable_type');
        }

        // Filtrer par profilable_type récupéré de l'utilisateur
        if ($profilableType !== null) {
            $query->where('roleable_type', $profilableType);
        }

        // Filtrer par profilable_id si fourni
        if ($profilableId !== null) {
            $query->where('roleable_id', $profilableId);
        }

        return $query->paginate($perPage, $columns);
    }
}

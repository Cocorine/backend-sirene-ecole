<?php

namespace App\Services;

use App\Repositories\Contracts\PermissionRepositoryInterface;
use App\Services\Contracts\PermissionServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * @property PermissionRepositoryInterface $repository
 */
class PermissionService extends BaseService implements PermissionServiceInterface
{
    public function __construct(PermissionRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    public function findBySlug(string $slug): JsonResponse
    {
        try {
            $permission = $this->repository->findBySlug($slug);
            if (!$permission) {
                return $this->notFoundResponse('Permission not found.');
            }
            return $this->successResponse('Permission found.', $permission);
        } catch (Exception $e) {
            Log::error("Error in PermissionService::findBySlug - " . $e->getMessage());
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function getByRole(string $roleId): JsonResponse
    {
        try {
            $permissions = $this->repository->getByRole($roleId);
            return $this->successResponse('Permissions found.', $permissions);
        } catch (Exception $e) {
            Log::error("Error in PermissionService::getByRole - " . $e->getMessage());
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}

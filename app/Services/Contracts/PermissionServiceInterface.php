<?php

namespace App\Services\Contracts;

use Illuminate\Http\JsonResponse;

interface PermissionServiceInterface extends BaseServiceInterface
{
    public function findBySlug(string $slug): JsonResponse;
    public function getByRole(string $roleId): JsonResponse;
}

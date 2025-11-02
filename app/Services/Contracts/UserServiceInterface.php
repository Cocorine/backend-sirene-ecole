<?php

namespace App\Services\Contracts;

use App\Models\User;
use Illuminate\Http\JsonResponse;

interface UserServiceInterface extends BaseServiceInterface
{
    public function findByIdentifier(string $identifier): JsonResponse;
    public function findByEmail(string $email): JsonResponse;
    public function findByPhone(string $phone): JsonResponse;
    public function findByUserAccount(string $accountType, string $accountId): JsonResponse;
    public function userExists(string $identifier): JsonResponse;
    public function updateStatus(string $id, int $status): JsonResponse;
    public function activateUser(string $id): JsonResponse;
    public function deactivateUser(string $id): JsonResponse;
    public function updatePassword(string $id, string $newPassword): JsonResponse;
}
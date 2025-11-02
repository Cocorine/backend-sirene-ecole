<?php

namespace App\Repositories\Contracts;

use App\Models\User;

interface UserRepositoryInterface extends BaseRepositoryInterface
{
    public function findByIdentifier(string $identifier): ?User;
    public function findByEmail(string $email): ?User;
    public function findByPhone(string $phone): ?User;
    public function findByUserAccount(string $accountType, string $accountId): ?User;
    public function userExists(string $identifier): bool;
    public function updateStatus(string $id, int $status): ?User;
    public function activateUser(string $id): ?User;
    public function deactivateUser(string $id): ?User;
    public function updatePassword(string $id, string $newPassword): ?User;
}
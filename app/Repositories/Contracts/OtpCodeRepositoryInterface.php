<?php

namespace App\Repositories\Contracts;

use App\Models\OtpCode;

interface OtpCodeRepositoryInterface extends BaseRepositoryInterface
{
    public function findByUserId(string $userId): ?OtpCode;
    public function findByCodeAndPhone(string $code, string $phone): ?OtpCode;
    public function findByTelephoneAndCode(string $telephone, string $code): ?OtpCode;
    public function markAsUsed(string $id): ?OtpCode;
    public function markAsVerified(string $id): ?OtpCode;
    public function deleteUnverifiedByPhone(string $telephone): int;
    public function deleteExpired(): int;
    public function incrementAttempts(string $id): ?OtpCode;
}
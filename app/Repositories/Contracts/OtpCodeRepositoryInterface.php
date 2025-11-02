<?php

namespace App\Repositories\Contracts;

use App\Models\OtpCode;

interface OtpCodeRepositoryInterface extends BaseRepositoryInterface
{
    public function findByUserId(string $userId): ?OtpCode;
    public function findByCodeAndPhone(string $code, string $phone): ?OtpCode;
    public function markAsUsed(string $id): ?OtpCode;
}
<?php

namespace App\Repositories\Contracts;

use App\Models\UserInfo;

interface UserInfoRepositoryInterface extends BaseRepositoryInterface
{
    public function findByUserId(string $userId): ?UserInfo;
    public function findByEmail(string $email): ?UserInfo;
    public function findByPhone(string $phone): ?UserInfo;
}
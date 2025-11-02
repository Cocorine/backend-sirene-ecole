<?php

namespace App\Repositories;

use App\Models\UserInfo;
use App\Repositories\Contracts\UserInfoRepositoryInterface;
use Exception;
use Illuminate\Support\Facades\Log;

class UserInfoRepository extends BaseRepository implements UserInfoRepositoryInterface
{
    public function __construct(UserInfo $model)
    {
        parent::__construct($model);
    }

    public function findByUserId(string $userId): ?UserInfo
    {
        try {
            return $this->model->where('user_id', $userId)->first();
        } catch (Exception $e) {
            Log::error("Error in " . get_class($this) . "::findByUserId - " . $e->getMessage());
            throw $e;
        }
    }

    public function findByEmail(string $email): ?UserInfo
    {
        try {
            return $this->model->where('email', $email)->first();
        } catch (Exception $e) {
            Log::error("Error in " . get_class($this) . "::findByEmail - " . $e->getMessage());
            throw $e;
        }
    }

    public function findByPhone(string $phone): ?UserInfo
    {
        try {
            return $this->model->where('telephone', $phone)->first();
        } catch (Exception $e) {
            Log::error("Error in " . get_class($this) . "::findByPhone - " . $e->getMessage());
            throw $e;
        }
    }
}
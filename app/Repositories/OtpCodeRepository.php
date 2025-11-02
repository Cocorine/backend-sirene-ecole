<?php

namespace App\Repositories;

use App\Models\OtpCode;
use App\Repositories\Contracts\OtpCodeRepositoryInterface;
use Exception;
use Illuminate\Support\Facades\Log;

class OtpCodeRepository extends BaseRepository implements OtpCodeRepositoryInterface
{
    public function __construct(OtpCode $model)
    {
        parent::__construct($model);
    }

    public function findByUserId(string $userId): ?OtpCode
    {
        try {
            return $this->model->where('user_id', $userId)
                               ->where('utilise', false)
                               ->where('expire_le', '>', now())
                               ->latest()
                               ->first();
        } catch (Exception $e) {
            Log::error("Error in " . get_class($this) . "::findByUserId - " . $e->getMessage());
            throw $e;
        }
    }

    public function findByCodeAndPhone(string $code, string $phone): ?OtpCode
    {
        try {
            return $this->model->where('code', $code)
                               ->where('telephone', $phone)
                               ->where('utilise', false)
                               ->where('expire_le', '>', now())
                               ->first();
        } catch (Exception $e) {
            Log::error("Error in " . get_class($this) . "::findByCodeAndPhone - " . $e->getMessage());
            throw $e;
        }
    }

    public function markAsUsed(string $id): ?OtpCode
    {
        try {
            $otpCode = $this->find($id);
            if ($otpCode) {
                $otpCode->utilise = true;
                $otpCode->date_verification = now();
                $otpCode->save();
            }
            return $otpCode;
        } catch (Exception $e) {
            Log::error("Error in " . get_class($this) . "::markAsUsed - " . $e->getMessage());
            throw $e;
        }
    }
}
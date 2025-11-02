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

    public function findByTelephoneAndCode(string $telephone, string $code): ?OtpCode
    {
        try {
            return $this->model->where('telephone', $telephone)
                               ->where('code', $code)
                               ->where('verifie', false)
                               ->first();
        } catch (Exception $e) {
            Log::error("Error in " . get_class($this) . "::findByTelephoneAndCode - " . $e->getMessage());
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

    public function markAsVerified(string $id): ?OtpCode
    {
        try {
            $otpCode = $this->find($id);
            if ($otpCode) {
                $otpCode->verifie = true;
                $otpCode->date_verification = now();
                $otpCode->save();
            }
            return $otpCode;
        } catch (Exception $e) {
            Log::error("Error in " . get_class($this) . "::markAsVerified - " . $e->getMessage());
            throw $e;
        }
    }

    public function deleteUnverifiedByPhone(string $telephone): int
    {
        try {
            return $this->model->where('telephone', $telephone)
                               ->where('verifie', false)
                               ->delete();
        } catch (Exception $e) {
            Log::error("Error in " . get_class($this) . "::deleteUnverifiedByPhone - " . $e->getMessage());
            throw $e;
        }
    }

    public function deleteExpired(): int
    {
        try {
            return $this->model->where('date_expiration', '<', now())
                               ->orWhere(function ($query) {
                                   $query->where('verifie', true)
                                         ->where('updated_at', '<', now()->subDays(1));
                               })
                               ->delete();
        } catch (Exception $e) {
            Log::error("Error in " . get_class($this) . "::deleteExpired - " . $e->getMessage());
            throw $e;
        }
    }

    public function incrementAttempts(string $id): ?OtpCode
    {
        try {
            $otpCode = $this->find($id);
            if ($otpCode) {
                $otpCode->increment('tentatives');
                $otpCode->refresh();
            }
            return $otpCode;
        } catch (Exception $e) {
            Log::error("Error in " . get_class($this) . "::incrementAttempts - " . $e->getMessage());
            throw $e;
        }
    }
}
<?php

namespace App\Services\Contracts;

use Illuminate\Http\JsonResponse;

interface OrdreMissionServiceInterface extends BaseServiceInterface
{
    public function getCandidaturesByOrdreMission(string $ordreMissionId): JsonResponse;
    public function getOrdreMissionsByVille(string $villeId): JsonResponse;
    public function cloturerCandidatures(string $ordreMissionId, string $adminId): JsonResponse;
    public function rouvrirCandidatures(string $ordreMissionId, string $adminId): JsonResponse;
}

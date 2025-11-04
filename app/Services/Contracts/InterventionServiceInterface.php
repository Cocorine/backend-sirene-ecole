<?php

namespace App\Services\Contracts;

use Illuminate\Http\JsonResponse;

interface InterventionServiceInterface extends BaseServiceInterface
{
    public function soumettreCandidatureMission(string $ordreMissionId, string $technicienId): JsonResponse;
    public function accepterCandidature(string $missionTechnicienId, string $adminId): JsonResponse;
    public function demarrerIntervention(string $interventionId): JsonResponse;
    public function redigerRapport(string $interventionId, array $rapportData): JsonResponse;
    public function noterIntervention(string $interventionId, int $note, ?string $commentaire): JsonResponse;
    public function noterRapport(string $rapportId, int $note, string $review): JsonResponse;
}

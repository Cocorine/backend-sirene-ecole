<?php

namespace App\Services;

use App\Repositories\Contracts\InterventionRepositoryInterface;
use App\Repositories\Contracts\MissionTechnicienRepositoryInterface;
use App\Repositories\Contracts\RapportInterventionRepositoryInterface;
use App\Repositories\Contracts\OrdreMissionRepositoryInterface;
use App\Services\Contracts\InterventionServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class InterventionService extends BaseService implements InterventionServiceInterface
{
    protected MissionTechnicienRepositoryInterface $missionRepository;
    protected RapportInterventionRepositoryInterface $rapportRepository;
    protected OrdreMissionRepositoryInterface $ordreMissionRepository;

    public function __construct(
        InterventionRepositoryInterface $repository,
        MissionTechnicienRepositoryInterface $missionRepository,
        RapportInterventionRepositoryInterface $rapportRepository,
        OrdreMissionRepositoryInterface $ordreMissionRepository
    ) {
        parent::__construct($repository);
        $this->missionRepository = $missionRepository;
        $this->rapportRepository = $rapportRepository;
        $this->ordreMissionRepository = $ordreMissionRepository;
    }

    public function soumettreCandidatureMission(string $ordreMissionId, string $technicienId): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Créer la candidature dans missions_techniciens
            $mission = $this->missionRepository->create([
                'ordre_mission_id' => $ordreMissionId,
                'technicien_id' => $technicienId,
                'statut' => 'en_attente',
            ]);

            DB::commit();
            return $this->createdResponse($mission);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Error in InterventionService::soumettreCandidatureMission - " . $e->getMessage());
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function accepterCandidature(string $missionTechnicienId, string $adminId): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Mettre à jour la candidature acceptée
            $missionTechnicien = $this->missionRepository->update($missionTechnicienId, [
                'statut' => 'acceptee',
                'date_acceptation' => now(),
            ]);

            // Récupérer la mission avec ses relations
            $missionTechnicien = $this->missionRepository->find($missionTechnicienId, relations: ['ordreMission']);

            // Mettre à jour l'ordre de mission
            $this->ordreMissionRepository->update($missionTechnicien->ordre_mission_id, [
                'technicien_id' => $missionTechnicien->technicien_id,
                'statut' => 'en_cours',
                'date_acceptation' => now(),
            ]);

            // Créer l'intervention
            $intervention = $this->repository->create([
                'panne_id' => $missionTechnicien->ordreMission->panne_id,
                'technicien_id' => $missionTechnicien->technicien_id,
                'ordre_mission_id' => $missionTechnicien->ordre_mission_id,
                'statut' => 'assignee',
                'date_assignation' => now(),
            ]);

            DB::commit();
            return $this->successResponse('Candidature acceptée et intervention créée.', $intervention);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Error in InterventionService::accepterCandidature - " . $e->getMessage());
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function demarrerIntervention(string $interventionId): JsonResponse
    {
        try {
            $intervention = $this->repository->update($interventionId, [
                'statut' => 'en_cours',
                'date_debut' => now(),
            ]);

            return $this->successResponse('Intervention démarrée.', $intervention);
        } catch (Exception $e) {
            Log::error("Error in InterventionService::demarrerIntervention - " . $e->getMessage());
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function redigerRapport(string $interventionId, array $rapportData): JsonResponse
    {
        try {
            DB::beginTransaction();

            $rapportData['intervention_id'] = $interventionId;
            $rapportData['date_rapport'] = now();
            $rapportData['date_soumission'] = now();
            $rapportData['statut'] = 'brouillon';

            $rapport = $this->rapportRepository->create($rapportData);

            // Terminer l'intervention
            $this->repository->update($interventionId, [
                'statut' => 'terminee',
                'date_fin' => now(),
            ]);

            DB::commit();
            return $this->createdResponse($rapport);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Error in InterventionService::redigerRapport - " . $e->getMessage());
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function noterIntervention(string $interventionId, int $note, ?string $commentaire): JsonResponse
    {
        try {
            $intervention = $this->repository->update($interventionId, [
                'note_ecole' => $note,
                'commentaire_ecole' => $commentaire,
            ]);

            return $this->successResponse('Intervention notée par l\'école.', $intervention);
        } catch (Exception $e) {
            Log::error("Error in InterventionService::noterIntervention - " . $e->getMessage());
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function noterRapport(string $rapportId, int $note, string $review): JsonResponse
    {
        try {
            $rapport = $this->rapportRepository->update($rapportId, [
                'review_note' => $note,
                'review_admin' => $review,
                'statut' => 'valide',
            ]);

            return $this->successResponse('Rapport noté par l\'admin.', $rapport);
        } catch (Exception $e) {
            Log::error("Error in InterventionService::noterRapport - " . $e->getMessage());
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}

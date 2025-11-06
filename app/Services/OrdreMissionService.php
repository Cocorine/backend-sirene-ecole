<?php

namespace App\Services;

use App\Enums\StatutOrdreMission;
use App\Repositories\Contracts\OrdreMissionRepositoryInterface;
use App\Repositories\Contracts\MissionTechnicienRepositoryInterface;
use App\Repositories\Contracts\PanneRepositoryInterface;
use App\Services\Contracts\OrdreMissionServiceInterface;
use App\Services\Contracts\NotificationServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class OrdreMissionService extends BaseService implements OrdreMissionServiceInterface
{
    protected MissionTechnicienRepositoryInterface $missionTechnicienRepository;
    protected PanneRepositoryInterface $panneRepository;
    protected NotificationServiceInterface $notificationService;

    public function __construct(
        OrdreMissionRepositoryInterface $repository,
        MissionTechnicienRepositoryInterface $missionTechnicienRepository,
        PanneRepositoryInterface $panneRepository,
        NotificationServiceInterface $notificationService
    ) {
        parent::__construct($repository);
        $this->missionTechnicienRepository = $missionTechnicienRepository;
        $this->panneRepository = $panneRepository;
        $this->notificationService = $notificationService;
    }

    // Override create() pour ajouter la logique métier spécifique
    public function create(array $data): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Ajouter les valeurs par défaut
            $data['statut'] = $data['statut'] ?? StatutOrdreMission::EN_ATTENTE;
            $data['date_generation'] = $data['date_generation'] ?? now();
            $data['nombre_techniciens_requis'] = $data['nombre_techniciens_requis'] ?? 1;
            $data['nombre_techniciens_acceptes'] = 0;

            // Générer le numéro d'ordre si non fourni
            if (!isset($data['numero_ordre'])) {
                $data['numero_ordre'] = $this->generateNumeroOrdre();
            }

            // Créer l'ordre de mission via le repository
            $ordreMission = $this->repository->create($data);

            // Mettre à jour le statut de la panne si panne_id est fourni
            if (isset($data['panne_id'])) {
                $this->panneRepository->update($data['panne_id'], [
                    'statut' => 'validee',
                ]);
            }

            // Notifier tous les techniciens de la ville
            if (isset($data['ville_id'])) {
                $this->notificationService->sendNewOrdreMissionNotificationToTechnicians(
                    $data['ville_id'],
                    [
                        'id' => $ordreMission->id,
                        'numero_ordre' => $ordreMission->numero_ordre,
                        'ville_nom' => $ordreMission->ville->nom ?? 'Ville Inconnue', // Assuming OrdreMission has a 'ville' relationship
                    ]
                );
            }

            DB::commit();
            return $this->createdResponse($ordreMission);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Error in OrdreMissionService::create - " . $e->getMessage());
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function getCandidaturesByOrdreMission(string $ordreMissionId): JsonResponse
    {
        try {
            $candidatures = $this->missionTechnicienRepository->findAllBy([
                'ordre_mission_id' => $ordreMissionId
            ]);

            return $this->successResponse(null, $candidatures);
        } catch (Exception $e) {
            Log::error("Error in OrdreMissionService::getCandidaturesByOrdreMission - " . $e->getMessage());
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function getOrdreMissionsByVille(string $villeId): JsonResponse
    {
        try {
            $ordresMission = $this->repository->findAllBy(['ville_id' => $villeId]);
            return $this->successResponse(null, $ordresMission);
        } catch (Exception $e) {
            Log::error("Error in OrdreMissionService::getOrdreMissionsByVille - " . $e->getMessage());
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function cloturerCandidatures(string $ordreMissionId, string $adminId): JsonResponse
    {
        try {
            $ordreMission = $this->repository->find($ordreMissionId);
            if (!$ordreMission) {
                return $this->notFoundResponse('Ordre de mission non trouvé.');
            }

            if ($ordreMission->candidature_cloturee) {
                return $this->errorResponse('Les candidatures sont déjà clôturées.', 400);
            }

            $ordreMission = $this->repository->update($ordreMissionId, [
                'candidature_cloturee' => true,
                'date_cloture_candidature' => now(),
                'cloture_par' => $adminId,
            ]);

            return $this->successResponse('Candidatures clôturées avec succès.', $ordreMission);
        } catch (Exception $e) {
            Log::error("Error in OrdreMissionService::cloturerCandidatures - " . $e->getMessage());
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function rouvrirCandidatures(string $ordreMissionId, string $adminId): JsonResponse
    {
        try {
            $ordreMission = $this->repository->find($ordreMissionId);
            if (!$ordreMission) {
                return $this->notFoundResponse('Ordre de mission non trouvé.');
            }

            if (!$ordreMission->candidature_cloturee) {
                return $this->errorResponse('Les candidatures ne sont pas clôturées.', 400);
            }

            $ordreMission = $this->repository->update($ordreMissionId, [
                'candidature_cloturee' => false,
                'date_cloture_candidature' => null,
                'cloture_par' => null,
            ]);

            return $this->successResponse('Candidatures réouvertes avec succès.', $ordreMission);
        } catch (Exception $e) {
            Log::error("Error in OrdreMissionService::rouvrirCandidatures - " . $e->getMessage());
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function rouvrirCandidatures(string $ordreMissionId, string $adminId): JsonResponse
    {
        try {
            $ordreMission = $this->repository->find($ordreMissionId);
            if (!$ordreMission) {
                return $this->notFoundResponse('Ordre de mission non trouvé.');
            }

            if (!$ordreMission->candidature_cloturee) {
                return $this->errorResponse('Les candidatures ne sont pas clôturées.', 400);
            }

            $ordreMission = $this->repository->update($ordreMissionId, [
                'candidature_cloturee' => false,
                'date_cloture_candidature' => null,
                'cloture_par' => null,
            ]);

            return $this->successResponse('Candidatures réouvertes avec succès.', $ordreMission);
        } catch (Exception $e) {
            Log::error("Error in OrdreMissionService::rouvrirCandidatures - " . $e->getMessage());
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function validerCandidature(string $missionTechnicienId): JsonResponse
    {
        try {
            DB::beginTransaction();

            $missionTechnicien = $this->missionTechnicienRepository->find($missionTechnicienId, relations: ['technicien', 'ordreMission']);
            if (!$missionTechnicien) {
                DB::rollBack();
                return $this->notFoundResponse('Candidature non trouvée.');
            }

            if ($missionTechnicien->statut === 'validee') {
                DB::rollBack();
                return $this->errorResponse('Cette candidature est déjà validée.', 400);
            }

            // Mettre à jour le statut de la candidature
            $this->missionTechnicienRepository->update($missionTechnicienId, [
                'statut' => 'validee',
                'date_validation' => now(),
            ]);

            // Incrémenter le nombre de techniciens acceptés dans l'ordre de mission
            $this->repository->update($missionTechnicien->ordre_mission_id, [
                'nombre_techniciens_acceptes' => DB::raw('nombre_techniciens_acceptes + 1'),
            ]);

            // Envoyer la notification au technicien
            $this->notificationService->sendCandidatureValidationNotification(
                $missionTechnicien->technicien_id,
                [
                    'id' => $missionTechnicien->id,
                    'ordre_mission_id' => $missionTechnicien->ordre_mission_id,
                    'numero_ordre' => $missionTechnicien->ordreMission->numero_ordre,
                ]
            );

            DB::commit();
            return $this->successResponse('Candidature validée avec succès.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Error in OrdreMissionService::validerCandidature - " . $e->getMessage());
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    private function generateNumeroOrdre(): string
    {
        do {
            $numero = 'OM-' . date('Ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(6));
        } while ($this->repository->findBy(['numero_ordre' => $numero]));

        return $numero;
    }
}

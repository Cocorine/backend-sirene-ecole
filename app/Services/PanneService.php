<?php

namespace App\Services;

use App\Enums\StatutPanne;
use App\Repositories\Contracts\OrdreMissionRepositoryInterface;
use App\Repositories\Contracts\PanneRepositoryInterface;
use App\Services\Contracts\PanneServiceInterface;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PanneService extends BaseService implements PanneServiceInterface
{
    protected OrdreMissionRepositoryInterface $ordreMissionRepository;

    public function __construct(
        PanneRepositoryInterface $repository,
        OrdreMissionRepositoryInterface $ordreMissionRepository
    ) {
        parent::__construct($repository);
        $this->ordreMissionRepository = $ordreMissionRepository;
    }

    public function validerPanne(string $panneId): JsonResponse
    {
        try {
            DB::beginTransaction();

            $panne = $this->repository->update($panneId, [
                'statut' => StatutPanne::VALIDEE,
                'date_validation' => now(),
            ]);

            // Fetch the panne with its site relationship
            $panneWithSite = $this->repository->find($panneId, ['site']);

            // Create OrdreMission
            $this->ordreMissionRepository->create([
                'panne_id' => $panneWithSite->id,
                'ville_id' => $panneWithSite->site->ville_id,
                'statut' => 'en_attente', // as per migration default
            ]);

            DB::commit();
            return $this->successResponse('Panne validée et ordre de mission créé.', $panne);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Error in PanneService::validerPanne - " . $e->getMessage());
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function cloturerPanne(string $panneId): JsonResponse
    {
        try {
            DB::beginTransaction();

            $panne = $this->repository->update($panneId, [
                'statut' => StatutPanne::CLOTUREE,
                'date_cloture' => now(),
            ]);

            DB::commit();
            return $this->successResponse('Panne clôturée avec succès.', $panne);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Error in PanneService::cloturerPanne - " . $e->getMessage());
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}

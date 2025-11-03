<?php

namespace App\Services;

use App\Enums\StatutAbonnement;
use App\Repositories\Contracts\AbonnementRepositoryInterface;
use App\Repositories\Contracts\PaiementRepositoryInterface;
use App\Services\Contracts\PaiementServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

class PaiementService extends BaseService implements PaiementServiceInterface
{
    protected AbonnementRepositoryInterface $abonnementRepository;

    public function __construct(
        PaiementRepositoryInterface $repository,
        AbonnementRepositoryInterface $abonnementRepository
    ) {
        parent::__construct($repository);
        $this->abonnementRepository = $abonnementRepository;
    }

    public function traiterPaiement(string $abonnementId, array $paiementData): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Vérifier que l'abonnement existe
            $abonnement = $this->abonnementRepository->find($abonnementId);
            if (!$abonnement) {
                DB::rollBack();
                return $this->notFoundResponse('Abonnement non trouvé.');
            }

            // Vérifier que l'abonnement n'est pas déjà payé
            if ($abonnement->statut->value === 'actif') {
                DB::rollBack();
                return $this->errorResponse('Cet abonnement a déjà été payé.', 400);
            }

            // Générer le numéro de transaction
            $numeroTransaction = $this->generateNumeroTransaction();

            // Créer le paiement
            $paiement = $this->repository->create([
                'abonnement_id' => $abonnementId,
                'ecole_id' => $abonnement->ecole_id,
                'numero_transaction' => $numeroTransaction,
                'montant' => $paiementData['montant'] ?? $abonnement->montant,
                'moyen' => $paiementData['moyen'],
                'statut' => 'en_attente',
                'reference_externe' => $paiementData['reference_externe'] ?? null,
                'metadata' => $paiementData['metadata'] ?? null,
                'date_paiement' => now(),
            ]);

            DB::commit();

            return $this->createdResponse($paiement);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Error in PaiementService::traiterPaiement - " . $e->getMessage());
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function validerPaiement(string $paiementId): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Récupérer le paiement
            $paiement = $this->repository->find($paiementId, relations: ['abonnement']);
            if (!$paiement) {
                DB::rollBack();
                return $this->notFoundResponse('Paiement non trouvé.');
            }

            // Mettre à jour le paiement
            $this->repository->update($paiementId, [
                'statut' => 'valide',
                'date_validation' => now(),
            ]);

            // Activer l'abonnement
            $this->abonnementRepository->update($paiement->abonnement_id, [
                'statut' => StatutAbonnement::ACTIF,
            ]);

            DB::commit();

            return $this->successResponse('Paiement validé et abonnement activé avec succès.');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Error in PaiementService::validerPaiement - " . $e->getMessage());
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function getPaiementsByAbonnement(string $abonnementId): JsonResponse
    {
        try {
            $paiements = $this->repository->findAllBy(['abonnement_id' => $abonnementId]);
            return $this->successResponse(null, $paiements);
        } catch (Exception $e) {
            Log::error("Error in PaiementService::getPaiementsByAbonnement - " . $e->getMessage());
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    private function generateNumeroTransaction(): string
    {
        do {
            $numero = 'TXN-' . date('Ymd') . '-' . strtoupper(Str::random(8));
        } while ($this->repository->exists(['numero_transaction' => $numero]));

        return $numero;
    }
}

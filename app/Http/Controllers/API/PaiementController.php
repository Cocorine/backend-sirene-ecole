<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\Contracts\PaiementServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaiementController extends Controller
{
    protected PaiementServiceInterface $paiementService;

    public function __construct(PaiementServiceInterface $paiementService)
    {
        $this->paiementService = $paiementService;
    }

    /**
     * Traiter un paiement pour un abonnement
     */
    public function traiter(Request $request, string $abonnementId): JsonResponse
    {
        $validated = $request->validate([
            'montant' => 'required|numeric|min:0',
            'moyen' => 'required|in:MOBILE_MONEY,CARTE_BANCAIRE,QR_CODE,VIREMENT',
            'reference_externe' => 'nullable|string',
            'metadata' => 'nullable|array',
        ]);

        return $this->paiementService->traiterPaiement($abonnementId, $validated);
    }

    /**
     * Valider un paiement (webhook ou admin)
     */
    public function valider(string $paiementId): JsonResponse
    {
        return $this->paiementService->validerPaiement($paiementId);
    }

    /**
     * Lister les paiements d'un abonnement
     */
    public function parAbonnement(string $abonnementId): JsonResponse
    {
        return $this->paiementService->getPaiementsByAbonnement($abonnementId);
    }

    /**
     * Afficher les détails d'un paiement
     */
    public function show(string $id): JsonResponse
    {
        return $this->paiementService->getById($id, relations: ['abonnement.ecole']);
    }

    /**
     * Lister tous les paiements (admin)
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 15);
        return $this->paiementService->getAll($perPage, ['abonnement', 'ecole']);
    }
}

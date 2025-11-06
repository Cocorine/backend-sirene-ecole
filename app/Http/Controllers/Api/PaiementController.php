<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Contracts\PaiementServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class PaiementController extends Controller
{
    protected PaiementServiceInterface $paiementService;

    public function __construct(PaiementServiceInterface $paiementService)
    {
        $this->paiementService = $paiementService;
    }

    /**
     * Traiter un paiement pour un abonnement
     *
     * @OA\Post(
     *     path="/api/paiements/abonnements/{abonnementId}",
     *     tags={"Paiements"},
     *     summary="Traiter un paiement pour un abonnement",
     *     description="Crée et traite un paiement pour un abonnement. Peut être appelé via QR code.",
     *     operationId="traiterPaiement",
     *     @OA\Parameter(
     *         name="abonnementId",
     *         in="path",
     *         required=true,
     *         description="ID de l'abonnement",
     *         @OA\Schema(type="string", example="01ARZ3NDEKTSV4RRFFQ69G5FAV")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Informations du paiement",
     *         @OA\JsonContent(
     *             required={"montant", "moyen"},
     *             @OA\Property(property="montant", type="number", example=50000, description="Montant du paiement"),
     *             @OA\Property(property="moyen", type="string", enum={"MOBILE_MONEY", "CARTE_BANCAIRE", "QR_CODE", "VIREMENT"}, example="MOBILE_MONEY", description="Moyen de paiement utilisé"),
     *             @OA\Property(property="reference_externe", type="string", example="CPAY-123456", description="Référence externe du paiement (ex: CinetPay transaction ID)"),
     *             @OA\Property(property="metadata", type="object", description="Métadonnées additionnelles")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Paiement traité avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Paiement traité avec succès"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="string", example="01ARZ3NDEKTSV4RRFFQ69G5FAV"),
     *                 @OA\Property(property="montant", type="number", example=50000),
     *                 @OA\Property(property="statut", type="string", example="valide"),
     *                 @OA\Property(property="moyen", type="string", example="MOBILE_MONEY")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Données invalides"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Abonnement non trouvé"
     *     )
     * )
     */
    public function traiter(Request $request, string $ecoleId, string $abonnementId = null): JsonResponse
    {
        $validated = $request->validate([
            'montant' => 'required|numeric|min:0',
            'moyen' => 'required|in:MOBILE_MONEY,CARTE_BANCAIRE,QR_CODE,VIREMENT',
            'reference_externe' => 'nullable|string',
            'metadata' => 'nullable|array',
        ]);

        if($abonnementId == null) $abonnementId = $ecoleId;

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

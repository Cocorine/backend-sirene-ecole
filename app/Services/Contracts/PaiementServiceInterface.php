<?php

namespace App\Services\Contracts;

use Illuminate\Http\JsonResponse;

interface PaiementServiceInterface extends BaseServiceInterface
{
    public function traiterPaiement(string $abonnementId, array $paiementData): JsonResponse;
    public function validerPaiement(string $paiementId): JsonResponse;
    public function getPaiementsByAbonnement(string $abonnementId): JsonResponse;
}

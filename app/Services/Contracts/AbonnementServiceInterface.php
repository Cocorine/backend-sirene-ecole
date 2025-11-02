<?php

namespace App\Services\Contracts;

use App\Models\Abonnement;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

interface AbonnementServiceInterface extends BaseServiceInterface
{

    /**
     * Récupérer l'abonnement actif d'une école
     */
    //public function getActiveByEcole(string $ecoleId): ?Abonnement;

    /**
     * Récupérer tous les abonnements d'une école
     */
    //public function getAllByEcole(string $ecoleId): Collection;

    /**
     * Renouveler un abonnement
     */
    //public function renew(string $abonnementId, array $data): Abonnement;

    /**
     * Générer le token crypté de l'abonnement
     */
    //public function generateToken(string $abonnementId): Abonnement;

    /**
     * Activer un abonnement après paiement
     */
    //public function activateAfterPayment(string $abonnementId): Abonnement;

    /**
     * Suspendre un abonnement
     */
    //public function suspend(string $abonnementId, string $raison): Abonnement;

    /**
     * Réactiver un abonnement suspendu
     */
    //public function reactivate(string $abonnementId): Abonnement;

    /**
     * Récupérer les abonnements expirant bientôt
     */
    //public function getExpiringSoon(int $days = 30): Collection;

    /**
     * Récupérer les abonnements expirés
     */
    //public function getExpired(): Collection;

    /**
     * Récupérer les abonnements actifs
     */
    //public function getActive(): Collection;

    /**
     * Marquer les abonnements expirés
     */
    //public function markExpiredSubscriptions(): int;

    /**
     * Vérifier la validité d'un abonnement
     */
    //public function isValid(string $abonnementId): bool;

    /**
     * Calculer le prix de renouvellement
     */
    //public function calculateRenewalPrice(string $abonnementId): float;

    /**
     * Obtenir l'historique des abonnements d'une école
     */
    //public function getHistory(string $ecoleId): Collection;
}

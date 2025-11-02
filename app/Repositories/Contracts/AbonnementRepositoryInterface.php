<?php

namespace App\Repositories\Contracts;

use App\Models\Abonnement;
use Illuminate\Database\Eloquent\Collection;

interface AbonnementRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Récupérer l'abonnement actif d'une école
     */
    public function getActiveByEcole(string $ecoleId): ?Abonnement;

    /**
     * Récupérer tous les abonnements d'une école
     */
    public function getAllByEcole(string $ecoleId): Collection;

    /**
     * Récupérer les abonnements expirant dans X jours
     */
    public function getExpiringSoon(int $days = 30): Collection;

    /**
     * Récupérer les abonnements expirés
     */
    public function getExpired(): Collection;

    /**
     * Vérifier si une école a un abonnement actif
     */
    public function hasActiveSubscription(string $ecoleId): bool;

    /**
     * Renouveler un abonnement
     */
    public function renew(string $abonnementId, array $data): Abonnement;
}

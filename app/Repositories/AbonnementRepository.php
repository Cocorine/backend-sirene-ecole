<?php

namespace App\Repositories;

use App\Models\Abonnement;
use App\Repositories\Contracts\AbonnementRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class AbonnementRepository extends BaseRepository implements AbonnementRepositoryInterface
{
    public function __construct(Abonnement $model)
    {
        parent::__construct($model);
    }

    public function getActiveByEcole(string $ecoleId): ?Abonnement
    {
        return $this->model
            ->where('ecole_id', $ecoleId)
            ->where('statut', 'actif')
            ->where('date_fin', '>=', now())
            ->with(['sirene', 'token'])
            ->latest()
            ->first();
    }

    public function getAllByEcole(string $ecoleId): Collection
    {
        return $this->model
            ->where('ecole_id', $ecoleId)
            ->with(['sirene', 'paiements'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getExpiringSoon(int $days = 30): Collection
    {
        $dateLimit = now()->addDays($days);

        return $this->model
            ->where('statut', 'actif')
            ->whereBetween('date_fin', [now(), $dateLimit])
            ->with(['ecole', 'sirene'])
            ->get();
    }

    public function getExpired(): Collection
    {
        return $this->model
            ->where('date_fin', '<', now())
            ->where('statut', '!=', 'expire')
            ->with(['ecole', 'sirene'])
            ->get();
    }

    public function hasActiveSubscription(string $ecoleId): bool
    {
        return $this->model
            ->where('ecole_id', $ecoleId)
            ->where('statut', 'actif')
            ->where('date_fin', '>=', now())
            ->exists();
    }

    public function renew(string $abonnementId, array $data): Abonnement
    {
        $oldAbonnement = $this->find($abonnementId);

        // Désactiver l'ancien abonnement
        $oldAbonnement->update(['statut' => 'expire']);

        // Créer le nouvel abonnement
        return $this->create(array_merge($data, [
            'ecole_id' => $oldAbonnement->ecole_id,
            'sirene_id' => $oldAbonnement->sirene_id,
        ]));
    }
}

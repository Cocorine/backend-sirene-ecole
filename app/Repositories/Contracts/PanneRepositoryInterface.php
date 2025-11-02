<?php

namespace App\Repositories\Contracts;

use App\Models\Panne;
use Illuminate\Database\Eloquent\Collection;

interface PanneRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Récupérer les pannes par école
     */
    public function getByEcole(string $ecoleId): Collection;

    /**
     * Récupérer les pannes par statut
     */
    public function getByStatut(string $statut): Collection;

    /**
     * Récupérer les pannes non assignées
     */
    public function getUnassigned(): Collection;

    /**
     * Récupérer les pannes par ville (pour les techniciens)
     */
    public function getByVille(string $villeId): Collection;

    /**
     * Assigner une panne à un technicien
     */
    public function assign(string $panneId, string $technicienId): Panne;

    /**
     * Valider une panne
     */
    public function validate(string $panneId, string $validateur): Panne;
}

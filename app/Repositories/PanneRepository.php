<?php

namespace App\Repositories;

use App\Models\Panne;
use App\Repositories\Contracts\PanneRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class PanneRepository extends BaseRepository implements PanneRepositoryInterface
{
    public function __construct(Panne $model)
    {
        parent::__construct($model);
    }

    public function getByEcole(string $ecoleId): Collection
    {
        return $this->model
            ->where('ecole_id', $ecoleId)
            ->with(['sirene', 'interventions.technicien'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getByStatut(string $statut): Collection
    {
        return $this->model
            ->where('statut', $statut)
            ->with(['ecole', 'sirene'])
            ->orderBy('priorite', 'desc')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function getUnassigned(): Collection
    {
        return $this->model
            ->whereIn('statut', ['declaree', 'validee'])
            ->with(['ecole.ville', 'sirene'])
            ->orderBy('priorite', 'desc')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function getByVille(string $villeId): Collection
    {
        return $this->model
            ->whereHas('ecole', function ($query) use ($villeId) {
                $query->where('ville_id', $villeId);
            })
            ->with(['ecole', 'sirene', 'interventions'])
            ->orderBy('statut')
            ->orderBy('priorite', 'desc')
            ->get();
    }

    public function assign(string $panneId, string $technicienId): Panne
    {
        $panne = $this->find($panneId);
        $panne->update(['statut' => 'assignee']);

        // Créer l'intervention
        $panne->interventions()->create([
            'technicien_id' => $technicienId,
            'statut' => 'assignee',
            'date_assignation' => now(),
        ]);

        return $panne->fresh(['interventions.technicien']);
    }

    public function validate(string $panneId, string $validateur): Panne
    {
        $panne = $this->find($panneId);
        $panne->update([
            'statut' => 'validee',
            'date_validation' => now(),
            'valide_par' => $validateur,
        ]);

        return $panne;
    }
}

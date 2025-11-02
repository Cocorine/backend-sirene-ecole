<?php

namespace App\Repositories;

use App\Models\Sirene;
use App\Repositories\Contracts\SireneRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class SireneRepository extends BaseRepository implements SireneRepositoryInterface
{
    public function __construct(Sirene $model)
    {
        parent::__construct($model);
    }

    public function findByNumeroSerie(string $numeroSerie, array $relations = [])
    {
        return $this->model->with($relations)
            ->where('numero_serie', $numeroSerie)
            ->first();
    }

    public function getSirenesDisponibles(array $relations = []): Collection
    {
        return $this->model->with($relations)
            ->where('statut', 'DISPONIBLE')
            ->whereNull('site_id')
            ->get();
    }

    public function affecterSireneASite(string $sireneId, string $siteId)
    {
        return $this->update($sireneId, [
            'site_id' => $siteId,
            'statut' => 'ACTIVE',
            'date_installation' => now(),
        ]);
    }
}

<?php

namespace App\Services;

use App\Repositories\Contracts\SireneRepositoryInterface;
use App\Services\Contracts\SireneServiceInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class SireneService extends BaseService implements SireneServiceInterface
{
    public function __construct(SireneRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    public function findByNumeroSerie(string $numeroSerie): ?Model
    {
        try {
            return $this->repository->findByNumeroSerie($numeroSerie);
        } catch (\Exception $e) {
            \Log::error("Error in " . get_class($this) . "::findByNumeroSerie - " . $e->getMessage());
            throw $e;
        }
    }

    public function getSirenesDisponibles(): Collection
    {
        try {
            return $this->repository->getSirenesDisponibles();
        } catch (\Exception $e) {
            \Log::error("Error in " . get_class($this) . "::getSirenesDisponibles - " . $e->getMessage());
            throw $e;
        }
    }

    public function affecterSireneASite(string $sireneId, string $siteId): Model
    {
        try {
            return $this->repository->affecterSireneASite($sireneId, $siteId);
        } catch (\Exception $e) {
            \Log::error("Error in " . get_class($this) . "::affecterSireneASite - " . $e->getMessage());
            throw $e;
        }
    }
}

<?php

namespace App\Services;

use App\Repositories\Contracts\SiteRepositoryInterface;
use App\Services\Contracts\SiteServiceInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class SiteService extends BaseService implements SiteServiceInterface
{
    public function __construct(SiteRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    public function getSitesByEcole(string $ecoleId): Collection
    {
        try {
            return $this->repository->getSitesByEcole($ecoleId);
        } catch (\Exception $e) {
            \Log::error("Error in " . get_class($this) . "::getSitesByEcole - " . $e->getMessage());
            throw $e;
        }
    }

    public function getSitePrincipal(string $ecoleId): ?Model
    {
        try {
            return $this->repository->getSitePrincipal($ecoleId);
        } catch (\Exception $e) {
            \Log::error("Error in " . get_class($this) . "::getSitePrincipal - " . $e->getMessage());
            throw $e;
        }
    }
}

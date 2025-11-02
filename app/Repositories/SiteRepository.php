<?php

namespace App\Repositories;

use App\Models\Site;
use App\Repositories\Contracts\SiteRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class SiteRepository extends BaseRepository implements SiteRepositoryInterface
{
    public function __construct(Site $model)
    {
        parent::__construct($model);
    }

    public function getSitesByEcole(string $ecoleId, array $relations = []): Collection
    {
        return $this->model->with($relations)
            ->where('ecole_principale_id', $ecoleId)
            ->get();
    }

    public function getSitePrincipal(string $ecoleId)
    {
        return $this->model
            ->where('ecole_principale_id', $ecoleId)
            ->where('est_principale', true)
            ->first();
    }
}

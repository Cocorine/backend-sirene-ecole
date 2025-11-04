<?php

namespace App\Repositories;

use App\Models\RapportIntervention;
use App\Repositories\Contracts\RapportInterventionRepositoryInterface;

class RapportInterventionRepository extends BaseRepository implements RapportInterventionRepositoryInterface
{
    public function __construct(RapportIntervention $model)
    {
        parent::__construct($model);
    }
}

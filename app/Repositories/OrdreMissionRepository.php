<?php

namespace App\Repositories;

use App\Models\OrdreMission;
use App\Repositories\Contracts\OrdreMissionRepositoryInterface;

class OrdreMissionRepository extends BaseRepository implements OrdreMissionRepositoryInterface
{
    public function __construct(OrdreMission $model)
    {
        parent::__construct($model);
    }
}

<?php

namespace App\Repositories;

use App\Models\MissionTechnicien;
use App\Repositories\Contracts\MissionTechnicienRepositoryInterface;

class MissionTechnicienRepository extends BaseRepository implements MissionTechnicienRepositoryInterface
{
    public function __construct(MissionTechnicien $model)
    {
        parent::__construct($model);
    }
}

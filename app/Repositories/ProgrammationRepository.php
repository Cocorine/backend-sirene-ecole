<?php

namespace App\Repositories;

use App\Models\Programmation;
use App\Repositories\Contracts\ProgrammationRepositoryInterface;

class ProgrammationRepository extends BaseRepository implements ProgrammationRepositoryInterface
{
    public function __construct(Programmation $model)
    {
        parent::__construct($model);
    }
}

<?php

namespace App\Repositories;

use App\Models\CalendrierScolaire;
use App\Repositories\Contracts\CalendrierScolaireRepositoryInterface;

class CalendrierScolaireRepository extends BaseRepository implements CalendrierScolaireRepositoryInterface
{
    public function __construct(CalendrierScolaire $model)
    {
        parent::__construct($model);
    }

    // Implement specific methods for CalendrierScolaireRepository here if needed
}
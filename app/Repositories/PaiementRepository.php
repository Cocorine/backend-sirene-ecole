<?php

namespace App\Repositories;

use App\Models\Paiement;
use App\Repositories\Contracts\PaiementRepositoryInterface;

class PaiementRepository extends BaseRepository implements PaiementRepositoryInterface
{
    public function __construct(Paiement $model)
    {
        parent::__construct($model);
    }
}

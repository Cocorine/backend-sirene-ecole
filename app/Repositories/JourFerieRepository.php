<?php

namespace App\Repositories;

use App\Models\JourFerie;
use App\Repositories\Contracts\JourFerieRepositoryInterface;

class JourFerieRepository extends BaseRepository implements JourFerieRepositoryInterface
{
    public function __construct(JourFerie $model)
    {
        parent::__construct($model);
    }

    // Implement specific methods for JourFerieRepository here if needed
}
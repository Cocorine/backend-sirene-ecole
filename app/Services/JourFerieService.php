<?php

namespace App\Services;

use App\Models\JourFerie;
use App\Repositories\Contracts\JourFerieRepositoryInterface;
use App\Services\Contracts\JourFerieServiceInterface;
use Illuminate\Database\Eloquent\Collection;

class JourFerieService extends BaseService implements JourFerieServiceInterface
{

    /**
     * @param JourFerieRepositoryInterface $repository
     */
    public function __construct(JourFerieRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }
}

<?php

namespace App\Services;

use App\Repositories\Contracts\JourFerieRepositoryInterface;
use App\Services\Contracts\JourFerieServiceInterface;

class JourFerieService extends BaseService implements JourFerieServiceInterface
{
    public function __construct(JourFerieRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    // Implement specific methods for JourFerieService here if needed
}
<?php

namespace App\Services;

use App\Repositories\Contracts\CalendrierScolaireRepositoryInterface;
use App\Services\Contracts\CalendrierScolaireServiceInterface;

class CalendrierScolaireService extends BaseService implements CalendrierScolaireServiceInterface
{
    public function __construct(CalendrierScolaireRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    // Implement specific methods for CalendrierScolaireService here if needed
}
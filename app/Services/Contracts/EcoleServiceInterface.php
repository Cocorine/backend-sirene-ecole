<?php

namespace App\Services\Contracts;

interface EcoleServiceInterface extends BaseServiceInterface
{
    public function inscrireEcole(array $ecoleData, array $sitesData, array $sirenesData);
}

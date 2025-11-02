<?php

namespace App\Repositories\Contracts;

interface EcoleRepositoryInterface extends BaseRepositoryInterface
{
    public function createEcoleWithSites(array $ecoleData, array $sitesData = []);
}

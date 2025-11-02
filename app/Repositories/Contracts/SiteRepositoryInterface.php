<?php

namespace App\Repositories\Contracts;

interface SiteRepositoryInterface extends BaseRepositoryInterface
{
    public function getSitesByEcole(string $ecoleId, array $relations = []);
    public function getSitePrincipal(string $ecoleId);
}

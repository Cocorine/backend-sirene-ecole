<?php

namespace App\Services\Contracts;

interface SiteServiceInterface extends BaseServiceInterface
{
    public function getSitesByEcole(string $ecoleId);
    public function getSitePrincipal(string $ecoleId);
}

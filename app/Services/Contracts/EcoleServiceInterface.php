<?php

namespace App\Services\Contracts;

use Illuminate\Http\JsonResponse;

interface EcoleServiceInterface extends BaseServiceInterface
{
    public function inscrireEcole(array $ecoleData, array $sitePrincipalData, array $sitesAnnexeData = []): JsonResponse;
}

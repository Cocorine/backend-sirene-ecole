<?php

namespace App\Services\Contracts;

use App\Models\Programmation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;

interface ProgrammationServiceInterface
{

    /**
     * @param int $sireneId
     * @return JsonResponse
     */
    public function getBySireneId(int $sireneId): JsonResponse;

    /**
     * Get effective programmations for a sirene on a specific date, considering holidays.
     *
     * @param int $sireneId
     * @param string $date (format Y-m-d)
     * @return JsonResponse
     */
    public function getEffectiveProgrammationsForSirene(int $sireneId, string $date): JsonResponse;
}

<?php

namespace App\Services\Contracts;

use Illuminate\Database\Eloquent\Model;

interface CalendrierScolaireServiceInterface extends BaseServiceInterface
{
    /**
     * Get all public holidays associated with a specific school calendar.
     *
     * @param string $calendrierScolaireId The ID of the school calendar.
     * @return \Illuminate\Http\JsonResponse
     */
    public function getJoursFeries(string $calendrierScolaireId): \Illuminate\Http\JsonResponse;

    /**
     * Calculate the number of school days for a given school calendar, excluding weekends, holidays, and vacation periods.
     *
     * @param string $calendrierScolaireId The ID of the school calendar.
     * @param string|null $ecoleId The ID of the school (optional).
     * @return \Illuminate\Http\JsonResponse
     */
    public function calculateSchoolDays(string $calendrierScolaireId, string $ecoleId = null): \Illuminate\Http\JsonResponse;

    // Add specific methods for CalendrierScolaireService here if needed
}
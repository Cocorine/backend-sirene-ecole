<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface JourFerieRepositoryInterface extends BaseRepositoryInterface
{
    // Add specific methods for JourFerieRepository here if needed

    /**
     * Check if a given date is a public holiday.
     *
     * @param string $date (format Y-m-d)
     * @return bool
     */
    public function isJourFerie(string $date): bool;

    /**
     * Get public holidays for a specific school.
     *
     * @param int $ecoleId
     * @return Collection
     */
    public function getJoursFeriesForEcole(int $ecoleId): Collection;
}

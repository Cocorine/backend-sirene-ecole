<?php

namespace App\Repositories;

use App\Models\JourFerie;
use App\Repositories\Contracts\JourFerieRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class JourFerieRepository extends BaseRepository implements JourFerieRepositoryInterface
{
    public function __construct(JourFerie $model)
    {
        parent::__construct($model);
    }

    // Implement specific methods for JourFerieRepository here if needed

    /**
     * Check if a given date is a public holiday.
     *
     * @param string $date (format Y-m-d)
     * @return bool
     */
    public function isJourFerie(string $date): bool
    {
        return $this->model->where('date', $date)->exists();
    }

    /**
     * Get public holidays for a specific school.
     *
     * @param int $ecoleId
     * @return Collection
     */
    public function getJoursFeriesForEcole(int $ecoleId): Collection
    {
        // Assuming JourFerie model has a relation to Ecole or a direct ecole_id field
        // If not, this method will need adjustment based on how holidays are linked to schools.
        return $this->model->where('ecole_id', $ecoleId)->get();
    }
}

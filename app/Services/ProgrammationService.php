<?php

namespace App\Services;

use App\Models\Programmation;
use App\Services\Contracts\ProgrammationServiceInterface;
use App\Repositories\Contracts\ProgrammationRepositoryInterface;
use App\Services\Contracts\JourFerieServiceInterface;
use Illuminate\Database\Eloquent\Collection;


class ProgrammationService extends BaseService implements ProgrammationServiceInterface
{
    /**
     * @var JourFerieServiceInterface
     */
    protected $jourFerieService;

    /**
     * @param ProgrammationRepositoryInterface $repository
     * @param JourFerieServiceInterface $jourFerieService
     */
    public function __construct(ProgrammationRepositoryInterface $repository, JourFerieServiceInterface $jourFerieService)
    {
        parent::__construct($repository);
        $this->jourFerieService = $jourFerieService;
    }

    /**
     * @param int $sireneId
     * @return Collection
     */
    public function getBySireneId(int $sireneId): Collection
    {
        return $this->repository->getBySireneId($sireneId);
    }

    /**
     * Get effective programmations for a sirene on a specific date, considering holidays.
     *
     * @param int $sireneId
     * @param string $date (format Y-m-d)
     * @return Collection
     */
    public function getEffectiveProgrammationsForSirene(int $sireneId, string $date): Collection
    {
        $programmations = $this->repository->getBySireneId($sireneId);
        $carbonDate = Carbon::parse($date);
        $dayOfWeek = $carbonDate->dayName; // e.g., 'Monday'

        $isHoliday = $this->jourFerieService->isJourFerie($date);

        return $programmations->filter(function (Programmation $programmation) use ($isHoliday, $dayOfWeek) {
            // Check if the programming is active for the current day of the week
            if (!in_array($dayOfWeek, $programmation->jour_semaine)) {
                return false;
            }

            // If it's a holiday and holidays are NOT included, filter it out
            if ($isHoliday && !$programmation->jours_feries_inclus) {
                return false;
            }

            // Further checks could include date_debut, date_fin, vacances, etc.
            // For now, we focus on jours_feries_inclus and jour_semaine

            return true;
        });
    }
}

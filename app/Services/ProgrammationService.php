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

        return $programmations->filter(function (Programmation $programmation) use ($isHoliday, $dayOfWeek, $date) {
            // Check if the programming is active for the current day of the week
            if (!in_array($dayOfWeek, $programmation->jour_semaine)) {
                return false;
            }

            $shouldIncludeHoliday = $programmation->jours_feries_inclus;

            // Check for specific holiday exceptions
            if (is_array($programmation->jours_feries_exceptions)) {
                foreach ($programmation->jours_feries_exceptions as $exception) {
                    if (isset($exception['date']) && $exception['date'] === $date) {
                        if (isset($exception['action'])) {
                            $shouldIncludeHoliday = ($exception['action'] === 'include');
                        }
                        break; // Found an exception for this date, no need to check further
                    }
                }
            }

            // If it's a holiday and the final decision is NOT to include it, filter it out
            if ($isHoliday && !$shouldIncludeHoliday) {
                return false;
            }

            // Further checks could include date_debut, date_fin, vacances, etc.
            // For now, we focus on jours_feries_inclus, jours_feries_exceptions and jour_semaine

            return true;
        });
    }
}

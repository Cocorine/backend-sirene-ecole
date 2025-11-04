<?php

namespace App\Services;

use App\Repositories\Contracts\CalendrierScolaireRepositoryInterface;
use App\Services\Contracts\CalendrierScolaireServiceInterface;
use Carbon\Carbon;

class CalendrierScolaireService extends BaseService implements CalendrierScolaireServiceInterface
{
    public function __construct(CalendrierScolaireRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    /**
     * Get all public holidays associated with a specific school calendar.
     *
     * @param string $calendrierScolaireId The ID of the school calendar.
     * @return JsonResponse
     */
    public function getJoursFeries(string $calendrierScolaireId): JsonResponse
    {
        try {
            $calendrierScolaire = $this->repository->find($calendrierScolaireId, relations: ['joursFeries']);

            if (!$calendrierScolaire) {
                return $this->notFoundResponse('School calendar not found.');
            }

            return $this->successResponse(null, $calendrierScolaire->joursFeries);
        } catch (Exception $e) {
            Log::error("Error in " . get_class($this) . "::getJoursFeries - " . $e->getMessage());
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Calculate the number of school days for a given school calendar, excluding weekends, holidays, and vacation periods.
     *
     * @param string $calendrierScolaireId The ID of the school calendar.
     * @param string|null $ecoleId The ID of the school (optional).
     * @return JsonResponse
     */
    public function calculateSchoolDays(string $calendrierScolaireId, string $ecoleId = null): JsonResponse
    {
        try {
            $calendrierScolaire = $this->repository->find($calendrierScolaireId, relations: ['joursFeries']);

            if (!$calendrierScolaire) {
                return $this->notFoundResponse('School calendar not found.');
            }

            $startDate = $calendrierScolaire->date_rentree;
            $endDate = $calendrierScolaire->date_fin_annee;
            $vacances = $calendrierScolaire->periodes_vacances;
            $joursFeries = $calendrierScolaire->joursFeries->pluck('date_ferie')->map(fn ($date) => $date->format('Y-m-d'))->toArray();

            if ($ecoleId) {
                $ecole = \App\Models\Ecole::with('joursFeries')->find($ecoleId);
                if ($ecole) {
                    $ecoleJoursFeries = $ecole->joursFeries;
                    foreach ($ecoleJoursFeries as $jourFerie) {
                        $date = $jourFerie->date_ferie->format('Y-m-d');
                        if ($jourFerie->actif) {
                            // Add holiday if not already in the list
                            if (!in_array($date, $joursFeries)) {
                                $joursFeries[] = $date;
                            }
                        } else {
                            // Remove holiday if it exists in the list
                            if (($key = array_search($date, $joursFeries)) !== false) {
                                unset($joursFeries[$key]);
                            }
                        }
                    }
                }
            }

            $schoolDays = 0;
            $currentDate = clone $startDate;

            while ($currentDate->lte($endDate)) {
                // Check if it's a weekend
                if ($currentDate->isWeekday()) {
                    $isHoliday = false;

                    // Check if it's a public holiday
                    if (in_array($currentDate->format('Y-m-d'), $joursFeries)) {
                        $isHoliday = true;
                    }

                    // Check if it's a vacation period
                    if (!$isHoliday) {
                        foreach ($vacances as $vacance) {
                            $vacanceStart = \Carbon\Carbon::parse($vacance['date_debut']);
                            $vacanceEnd = \Carbon\Carbon::parse($vacance['date_fin']);
                            if ($currentDate->between($vacanceStart, $vacanceEnd)) {
                                $isHoliday = true;
                                break;
                            }
                        }
                    }

                    if (!$isHoliday) {
                        $schoolDays++;
                    }
                }
                $currentDate->addDay();
            }

            return $this->successResponse(null, ['school_days' => $schoolDays]);
        } catch (Exception $e) {
            Log::error("Error in " . get_class($this) . "::calculateSchoolDays - " . $e->getMessage());
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    // Implement specific methods for CalendrierScolaireService here if needed
}
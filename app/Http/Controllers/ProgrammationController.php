<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProgrammationRequest;
use App\Http\Requests\UpdateProgrammationRequest;
use App\Models\Programmation;
use App\Models\Sirene;
use App\Services\Contracts\ProgrammationServiceInterface;
use App\Traits\JsonResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProgrammationController extends Controller
{
    use JsonResponseTrait;

    /**
     * @var ProgrammationServiceInterface
     */
    protected $programmationService;

    /**
     * @param ProgrammationServiceInterface $programmationService
     */
    public function __construct(ProgrammationServiceInterface $programmationService)
    {
        $this->programmationService = $programmationService;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Sirene $sirene
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Sirene $sirene, Request $request): JsonResponse
    {
        $date = $request->query('date');

        if ($date) {
            $programmations = $this->programmationService->getEffectiveProgrammationsForSirene($sirene->id, $date);
        } else {
            $programmations = $this->programmationService->getBySireneId($sirene->id);
        }

        return $this->success($programmations);
    }

    /**
     * @param StoreProgrammationRequest $request
     * @param Sirene $sirene
     * @return JsonResponse
     */
    public function store(StoreProgrammationRequest $request, Sirene $sirene): JsonResponse
    {
        $data = array_merge($request->validated(), ['sirene_id' => $sirene->id]);
        $programmation = $this->programmationService->create($data);
        return $this->created($programmation);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function show(Sirene $sirene, Programmation $programmation): JsonResponse
    {
        return $this->success($programmation);
    }

    /**
     * @param UpdateProgrammationRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateProgrammationRequest $request, Sirene $sirene, Programmation $programmation): JsonResponse
    {
        $this->programmationService->update($programmation->id, $request->validated());
        return $this->noContent();
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(Sirene $sirene, Programmation $programmation): JsonResponse
    {
        $this->programmationService->delete($programmation->id);
        return $this->noContent();
    }
}

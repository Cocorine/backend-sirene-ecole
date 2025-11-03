<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Contracts\CalendrierScolaireServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Calendrier Scolaire",
 *     description="API Endpoints for School Calendar Management"
 * )
 */
class CalendrierScolaireController extends Controller
{
    protected $calendrierScolaireService;

    public function __construct(CalendrierScolaireServiceInterface $calendrierScolaireService)
    {
        $this->calendrierScolaireService = $calendrierScolaireService;
    }

    /**
     * Display a listing of the school calendar entries.
     *
     * @OA\Get(
     *     path="/api/calendrier-scolaire",
     *     summary="List all school calendar entries",
     *     tags={"Calendrier Scolaire"},
     *     security={ {"passport": {}} },
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of entries per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/CalendrierScolaire"))
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 15);
        return $this->calendrierScolaireService->paginate($perPage);
    }

    /**
     * Store a newly created school calendar entry in storage.
     *
     * @OA\Post(
     *     path="/api/calendrier-scolaire",
     *     summary="Create a new school calendar entry",
     *     tags={"Calendrier Scolaire"},
     *     security={ {"passport": {}} },
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CreateCalendrierScolaireRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="School calendar entry created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/CalendrierScolaire")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid.")
     *         )
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        return $this->calendrierScolaireService->create($request->all());
    }

    /**
     * Display the specified school calendar entry.
     *
     * @OA\Get(
     *     path="/api/calendrier-scolaire/{id}",
     *     summary="Get a specific school calendar entry by ID",
     *     tags={"Calendrier Scolaire"},
     *     security={ {"passport": {}} },
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the school calendar entry",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/CalendrierScolaire")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Entry not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Entry not found.")
     *         )
     *     )
     * )
     */
    public function show(string $id): JsonResponse
    {
        return $this->calendrierScolaireService->find($id);
    }

    /**
     * Update the specified school calendar entry in storage.
     *
     * @OA\Put(
     *     path="/api/calendrier-scolaire/{id}",
     *     summary="Update a specific school calendar entry by ID",
     *     tags={"Calendrier Scolaire"},
     *     security={ {"passport": {}} },
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the school calendar entry",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpdateCalendrierScolaireRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="School calendar entry updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/CalendrierScolaire")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Entry not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Entry not found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid.")
     *         )
     *     )
     * )
     */
    public function update(Request $request, string $id): JsonResponse
    {
        return $this->calendrierScolaireService->update($id, $request->all());
    }

    /**
     * Remove the specified school calendar entry from storage.
     *
     * @OA\Delete(
     *     path="/api/calendrier-scolaire/{id}",
     *     summary="Delete a specific school calendar entry by ID",
     *     tags={"Calendrier Scolaire"},
     *     security={ {"passport": {}} },
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the school calendar entry",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="School calendar entry deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Entry not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Entry not found.")
     *         )
     *     )
     * )
     */
    public function destroy(string $id): JsonResponse
    {
        return $this->calendrierScolaireService->delete($id);
    }
}

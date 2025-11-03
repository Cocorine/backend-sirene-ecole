<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Contracts\JourFerieServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Jours Fériés",
 *     description="API Endpoints for Public Holidays Management"
 * )
 */
class JourFerieController extends Controller
{
    protected $jourFerieService;

    public function __construct(JourFerieServiceInterface $jourFerieService)
    {
        $this->jourFerieService = $jourFerieService;
    }

    /**
     * Display a listing of the public holidays.
     *
     * @OA\Get(
     *     path="/api/jours-feries",
     *     summary="List all public holidays",
     *     tags={"Jours Fériés"},
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
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/JourFerie"))
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
        return $this->jourFerieService->paginate($perPage);
    }

    /**
     * Store a newly created public holiday in storage.
     *
     * @OA\Post(
     *     path="/api/jours-feries",
     *     summary="Create a new public holiday",
     *     tags={"Jours Fériés"},
     *     security={ {"passport": {}} },
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CreateJourFerieRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Public holiday created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/JourFerie")
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
        return $this->jourFerieService->create($request->all());
    }

    /**
     * Display the specified public holiday.
     *
     * @OA\Get(
     *     path="/api/jours-feries/{id}",
     *     summary="Get a specific public holiday by ID",
     *     tags={"Jours Fériés"},
     *     security={ {"passport": {}} },
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the public holiday",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/JourFerie")
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
        return $this->jourFerieService->find($id);
    }

    /**
     * Update the specified public holiday in storage.
     *
     * @OA\Put(
     *     path="/api/jours-feries/{id}",
     *     summary="Update a specific public holiday by ID",
     *     tags={"Jours Fériés"},
     *     security={ {"passport": {}} },
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the public holiday",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpdateJourFerieRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Public holiday updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/JourFerie")
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
        return $this->jourFerieService->update($id, $request->all());
    }

    /**
     * Remove the specified public holiday from storage.
     *
     * @OA\Delete(
     *     path="/api/jours-feries/{id}",
     *     summary="Delete a specific public holiday by ID",
     *     tags={"Jours Fériés"},
     *     security={ {"passport": {}} },
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the public holiday",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Public holiday deleted successfully"
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
        return $this->jourFerieService->delete($id);
    }
}

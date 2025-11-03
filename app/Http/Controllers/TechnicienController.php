<?php

namespace App\Http\Controllers;

use App\Http\Requests\Technicien\CreateTechnicienRequest;
use App\Http\Requests\Technicien\UpdateTechnicienRequest;
use App\Repositories\Contracts\TechnicienRepositoryInterface;
use App\Traits\JsonResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TechnicienController extends Controller
{
    use JsonResponseTrait;

    protected $technicienRepository;

    public function __construct(TechnicienRepositoryInterface $technicienRepository)
    {
        $this->technicienRepository = $technicienRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        try {
            $techniciens = $this->technicienRepository->all();
            return $this->successResponse('Liste des techniciens', $techniciens);
        } catch (\Exception $e) {
            return $this->errorResponse('Erreur lors de la récupération des techniciens', $e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateTechnicienRequest $request): JsonResponse
    {
        try {
            $technicien = $this->technicienRepository->create($request->validated());
            return $this->successResponse('Technicien créé avec succès', $technicien, 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Erreur lors de la création du technicien', $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $technicien = $this->technicienRepository->find($id);
            if (!$technicien) {
                return $this->errorResponse('Technicien non trouvé', null, 404);
            }
            return $this->successResponse('Détails du technicien', $technicien);
        } catch (\Exception $e) {
            return $this->errorResponse('Erreur lors de la récupération du technicien', $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTechnicienRequest $request, string $id): JsonResponse
    {
        try {
            $technicien = $this->technicienRepository->update($id, $request->validated());
            if (!$technicien) {
                return $this->errorResponse('Technicien non trouvé', null, 404);
            }
            return $this->successResponse('Technicien mis à jour avec succès', $technicien);
        } catch (\Exception $e) {
            return $this->errorResponse('Erreur lors de la mise à jour du technicien', $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $deleted = $this->technicienRepository->delete($id);
            if (!$deleted) {
                return $this->errorResponse('Technicien non trouvé', null, 404);
            }
            return $this->successResponse('Technicien supprimé avec succès', null, 204);
        } catch (\Exception $e) {
            return $this->errorResponse('Erreur lors de la suppression du technicien', $e->getMessage());
        }
    }
}
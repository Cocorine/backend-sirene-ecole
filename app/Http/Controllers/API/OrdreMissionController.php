<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\Contracts\OrdreMissionServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrdreMissionController extends Controller
{
    protected OrdreMissionServiceInterface $ordreMissionService;

    public function __construct(OrdreMissionServiceInterface $ordreMissionService)
    {
        $this->ordreMissionService = $ordreMissionService;
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 15);
        return $this->ordreMissionService->getAll($perPage, ['panne', 'ville', 'validePar', 'interventions.technicien']);
    }

    public function show(string $id): JsonResponse
    {
        return $this->ordreMissionService->getById($id, ['panne.sirene', 'ville', 'validePar', 'interventions.technicien', 'missionsTechniciens.technicien']);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'panne_id' => 'required|string|exists:pannes,id',
            'ville_id' => 'required|string|exists:villes,id',
            'valide_par' => 'required|string|exists:users,id',
            'date_debut_candidature' => 'nullable|date',
            'date_fin_candidature' => 'nullable|date|after:date_debut_candidature',
            'nombre_techniciens_requis' => 'nullable|integer|min:1',
            'commentaire' => 'nullable|string',
        ]);

        return $this->ordreMissionService->create($validated);
    }

    public function getCandidatures(string $ordreMissionId): JsonResponse
    {
        return $this->ordreMissionService->getCandidaturesByOrdreMission($ordreMissionId);
    }

    public function getByVille(string $villeId): JsonResponse
    {
        return $this->ordreMissionService->getOrdreMissionsByVille($villeId);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'statut' => 'sometimes|string|in:en_attente,en_cours,termine,cloture',
            'date_debut_candidature' => 'nullable|date',
            'date_fin_candidature' => 'nullable|date|after:date_debut_candidature',
            'nombre_techniciens_requis' => 'nullable|integer|min:1',
            'commentaire' => 'nullable|string',
        ]);

        return $this->ordreMissionService->update($id, $validated);
    }

    public function destroy(string $id): JsonResponse
    {
        return $this->ordreMissionService->delete($id);
    }

    public function cloturerCandidatures(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'admin_id' => 'required|string|exists:users,id',
        ]);

        return $this->ordreMissionService->cloturerCandidatures($id, $validated['admin_id']);
    }

    public function rouvrirCandidatures(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'admin_id' => 'required|string|exists:users,id',
        ]);

        return $this->ordreMissionService->rouvrirCandidatures($id, $validated['admin_id']);
    }
}

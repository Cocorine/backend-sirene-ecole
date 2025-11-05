<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\Contracts\InterventionServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InterventionController extends Controller
{
    protected InterventionServiceInterface $interventionService;

    public function __construct(InterventionServiceInterface $interventionService)
    {
        $this->interventionService = $interventionService;
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 15);
        return $this->interventionService->getAll($perPage, ['technicien', 'panne', 'ordreMission']);
    }

    public function show(string $id): JsonResponse
    {
        return $this->interventionService->getById($id, ['technicien', 'panne', 'rapport']);
    }

    public function soumettreCandidature(Request $request, string $ordreMissionId): JsonResponse
    {
        $validated = $request->validate([
            'technicien_id' => 'required|string|exists:techniciens,id',
        ]);

        return $this->interventionService->soumettreCandidatureMission($ordreMissionId, $validated['technicien_id']);
    }

    public function accepterCandidature(Request $request, string $missionTechnicienId): JsonResponse
    {
        $validated = $request->validate([
            'admin_id' => 'required|string|exists:users,id',
        ]);

        return $this->interventionService->accepterCandidature($missionTechnicienId, $validated['admin_id']);
    }

    public function refuserCandidature(Request $request, string $missionTechnicienId): JsonResponse
    {
        $validated = $request->validate([
            'admin_id' => 'required|string|exists:users,id',
        ]);

        return $this->interventionService->refuserCandidature($missionTechnicienId, $validated['admin_id']);
    }

    public function retirerCandidature(Request $request, string $missionTechnicienId): JsonResponse
    {
        $validated = $request->validate([
            'motif_retrait' => 'required|string',
        ]);

        return $this->interventionService->retirerCandidature($missionTechnicienId, $validated['motif_retrait']);
    }

    public function retirerMission(Request $request, string $interventionId): JsonResponse
    {
        $validated = $request->validate([
            'motif_retrait' => 'required|string',
            'admin_id' => 'required|string|exists:users,id',
        ]);

        return $this->interventionService->retirerMissionTechnicien(
            $interventionId,
            $validated['motif_retrait'],
            $validated['admin_id']
        );
    }

    public function demarrer(string $interventionId): JsonResponse
    {
        return $this->interventionService->demarrerIntervention($interventionId);
    }

    public function redigerRapport(Request $request, string $interventionId): JsonResponse
    {
        $validated = $request->validate([
            'rapport' => 'required|string',
            'diagnostic' => 'nullable|string',
            'travaux_effectues' => 'nullable|string',
            'pieces_utilisees' => 'nullable|string',
            'resultat' => 'required|in:resolu,partiellement_resolu,non_resolu',
            'recommandations' => 'nullable|string',
            'photos' => 'nullable|array',
        ]);

        return $this->interventionService->redigerRapport($interventionId, $validated);
    }

    public function noterIntervention(Request $request, string $interventionId): JsonResponse
    {
        $validated = $request->validate([
            'note' => 'required|integer|min:1|max:5',
            'commentaire' => 'nullable|string',
        ]);

        return $this->interventionService->noterIntervention(
            $interventionId,
            $validated['note'],
            $validated['commentaire'] ?? null
        );
    }

    public function noterRapport(Request $request, string $rapportId): JsonResponse
    {
        $validated = $request->validate([
            'note' => 'required|integer|min:1|max:5',
            'review' => 'required|string',
        ]);

        return $this->interventionService->noterRapport($rapportId, $validated['note'], $validated['review']);
    }
}

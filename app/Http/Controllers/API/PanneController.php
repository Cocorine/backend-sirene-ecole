<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Sirene;
use App\Services\Contracts\PanneServiceInterface;
use Illuminate\Http\Request;

class PanneController extends Controller
{
    protected PanneServiceInterface $panneService;

    public function __construct(PanneServiceInterface $panneService)
    {
        $this->panneService = $panneService;
    }

    public function declarer(Request $request, $sireneId)
    {
        $sirene = Sirene::findOrFail($sireneId);

        $validated = $request->validate([
            'description' => 'required|string',
            'priorite' => 'sometimes|string|in:faible,moyenne,haute',
        ]);

        $panne = $sirene->declarerPanne($validated['description'], $validated['priorite'] ?? 'moyenne');

        return response()->json($panne, 201);
    }

    public function valider(Request $request, $panneId)
    {
        $validated = $request->validate([
            'admin_id' => 'required|string|exists:users,id',
        ]);

        return $this->panneService->validerPanne($panneId, $validated['admin_id']);
    }

    public function cloturer($panneId)
    {
        return $this->panneService->cloturerPanne($panneId);
    }
}
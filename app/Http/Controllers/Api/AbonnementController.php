<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Abonnement\UpdateAbonnementRequest;
use App\Services\Contracts\AbonnementServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AbonnementController extends Controller
{
    protected AbonnementServiceInterface $abonnementService;

    public function __construct(AbonnementServiceInterface $abonnementService)
    {
        $this->abonnementService = $abonnementService;
    }

    /**
     * Afficher les détails d'un abonnement (via QR Code)
     */
    public function details(string $id): JsonResponse
    {
        Gate::authorize('voir_abonnement');

        return $this->abonnementService->getById($id, relations: [
            'ecole',
            'site',
            'sirene',
            'paiements',
            'token'
        ]);
    }

    /**
     * Afficher la page de paiement d'un abonnement (via QR Code)
     */
    public function paiement(string $id): JsonResponse
    {
        Gate::authorize('voir_abonnement');

        return $this->abonnementService->getById($id, relations: [
            'ecole',
            'site',
            'sirene'
        ]);
    }

    /**
     * Lister tous les abonnements (admin)
     */
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('voir_les_abonnements');

        $perPage = $request->get('per_page', 15);
        return $this->abonnementService->getAll($perPage, ['ecole', 'site', 'sirene']);
    }

    /**
     * Afficher un abonnement
     */
    public function show(string $id): JsonResponse
    {
        Gate::authorize('voir_abonnement');

        return $this->abonnementService->getById($id, relations: [
            'ecole',
            'site',
            'sirene',
            'paiements',
            'token'
        ]);
    }

    /**
     * Mettre à jour un abonnement avec validation métier
     */
    public function update(UpdateAbonnementRequest $request, string $id): JsonResponse
    {
        Gate::authorize('modifier_abonnement');
        return $this->abonnementService->update($id, $request->validated());
    }
    /**
     * Supprimer un abonnement
     */
    public function destroy(string $id): JsonResponse
    {
        Gate::authorize('supprimer_abonnement');
        return $this->abonnementService->delete($id);
    }

    // ========== GESTION DU CYCLE DE VIE ==========

    public function renouveler(string $id): JsonResponse
    {
        Gate::authorize('modifier_abonnement');
        return $this->abonnementService->renouvelerAbonnement($id);
    }

    public function suspendre(Request $request, string $id): JsonResponse
    {
        Gate::authorize('modifier_abonnement');
        $validated = $request->validate(['raison' => 'required|string']);
        return $this->abonnementService->suspendre($id, $validated['raison']);
    }

    public function reactiver(string $id): JsonResponse
    {
        Gate::authorize('modifier_abonnement');

        return $this->abonnementService->reactiver($id);
    }

    public function annuler(Request $request, string $id): JsonResponse
    {
        Gate::authorize('modifier_abonnement');

        $validated = $request->validate(['raison' => 'required|string']);
        return $this->abonnementService->annuler($id, $validated['raison']);
    }

    // ========== RECHERCHE ==========

    public function getActif(string $ecoleId): JsonResponse
    {
        Gate::authorize('voir_les_abonnements');

        return $this->abonnementService->getAbonnementActif($ecoleId);
    }

    public function parEcole(string $ecoleId): JsonResponse
    {
        Gate::authorize('voir_les_abonnements');

        return $this->abonnementService->getAbonnementsByEcole($ecoleId);
    }

    public function parSirene(string $sireneId): JsonResponse
    {
        Gate::authorize('voir_les_abonnements');
        return $this->abonnementService->getAbonnementsBySirene($sireneId);
    }

    public function expirantBientot(Request $request): JsonResponse
    {
        $jours = $request->get('jours', 30);
        return $this->abonnementService->getExpirantBientot($jours);
    }

    public function expires(): JsonResponse
    {
        return $this->abonnementService->getExpires();
    }

    public function actifs(): JsonResponse
    {
        return $this->abonnementService->getActifs();
    }

    public function enAttente(): JsonResponse
    {
        return $this->abonnementService->getEnAttente();
    }

    // ========== VÉRIFICATIONS ==========

    public function estValide(string $id): JsonResponse
    {
        return $this->abonnementService->estValide($id);
    }

    public function ecoleAAbonnementActif(string $ecoleId): JsonResponse
    {
        return $this->abonnementService->ecoleAAbonnementActif($ecoleId);
    }

    public function peutEtreRenouvele(string $id): JsonResponse
    {
        return $this->abonnementService->peutEtreRenouvele($id);
    }

    // ========== STATISTIQUES ==========

    public function statistiques(): JsonResponse
    {
        return $this->abonnementService->getStatistiques();
    }

    public function revenusPeriode(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after:date_debut',
        ]);
        return $this->abonnementService->getRevenusPeriode($validated['date_debut'], $validated['date_fin']);
    }

    public function tauxRenouvellement(): JsonResponse
    {
        return $this->abonnementService->getTauxRenouvellement();
    }

    // ========== CALCULS ==========

    public function prixRenouvellement(string $id): JsonResponse
    {
        return $this->abonnementService->calculerPrixRenouvellement($id);
    }

    public function joursRestants(string $id): JsonResponse
    {
        return $this->abonnementService->getJoursRestants($id);
    }

    // ========== TÂCHES AUTOMATIQUES (CRON) ==========

    public function marquerExpires(): JsonResponse
    {
        return $this->abonnementService->marquerExpires();
    }

    public function envoyerNotifications(): JsonResponse
    {
        return $this->abonnementService->envoyerNotificationsExpiration();
    }

    public function autoRenouveler(): JsonResponse
    {
        return $this->abonnementService->autoRenouveler();
    }
}

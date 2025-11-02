<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ecole\InscriptionEcoleRequest;
use App\Services\Contracts\EcoleServiceInterface;
use Illuminate\Http\Request;

class EcoleController extends Controller
{
    protected $ecoleService;

    public function __construct(EcoleServiceInterface $ecoleService)
    {
        $this->ecoleService = $ecoleService;
    }

    /**
     * Inscription d'une nouvelle école
     */
    public function inscrire(InscriptionEcoleRequest $request)
    {
        try {
            // Préparer les données pour l'école
            $ecoleData = [
                'nom' => $request->nom,
                'nom_complet' => $request->nom_complet,
                'email' => $request->email,
                'telephone' => $request->telephone,
                'email_contact' => $request->email_contact,
                'telephone_contact' => $request->telephone_contact,
                'adresse' => $request->adresse,
                'types_etablissement' => $request->types_etablissement,
                'responsable_nom' => $request->responsable_nom,
                'responsable_prenom' => $request->responsable_prenom,
                'responsable_telephone' => $request->responsable_telephone,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'date_inscription' => now(),
            ];

            // Sites additionnels
            $sitesData = $request->sites ?? [];

            // Sirènes à affecter
            $sirenesData = $request->sirenes;

            // Inscrire l'école
            $ecole = $this->ecoleService->inscrireEcole($ecoleData, $sitesData, $sirenesData);

            return response()->json([
                'success' => true,
                'message' => 'École inscrite avec succès.',
                'data' => [
                    'ecole' => [
                        'id' => $ecole->id,
                        'nom' => $ecole->nom,
                        'email' => $ecole->email,
                        'telephone' => $ecole->telephone,
                        'code_etablissement' => $ecole->code_etablissement,
                        'identifiant' => $ecole->user->identifiant ?? null,
                        'mot_de_passe_temporaire' => $ecole->mot_de_passe_temporaire,
                    ],
                    'sites' => $ecole->sites->map(function ($site) {
                        return [
                            'id' => $site->id,
                            'nom' => $site->nom,
                            'est_principale' => $site->est_principale,
                            'adresse' => $site->adresse,
                            'sirene' => $site->sirene ? [
                                'numero_serie' => $site->sirene->numero_serie,
                                'modele' => $site->sirene->modeleSirene->nom ?? null,
                            ] : null,
                        ];
                    }),
                    'abonnement' => $ecole->abonnementActif ? [
                        'id' => $ecole->abonnementActif->id,
                        'numero_abonnement' => $ecole->abonnementActif->numero_abonnement,
                        'date_debut' => $ecole->abonnementActif->date_debut,
                        'date_fin' => $ecole->abonnementActif->date_fin,
                        'statut' => $ecole->abonnementActif->statut,
                        'montant' => $ecole->abonnementActif->montant,
                    ] : null,
                ],
                'note' => 'Veuillez conserver l\'identifiant et le mot de passe temporaire. Changez le mot de passe lors de la première connexion.',
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'inscription de l\'école.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtenir les informations de l'école connectée
     */
    public function show(Request $request)
    {
        $user = $request->user();

        if ($user->type !== 'ECOLE') {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé.',
            ], 403);
        }

        try {
            $ecole = $this->ecoleService->findById($user->user_account_type_id, [
                'sites.sirene.modeleSirene',
                'abonnementActif',
                'abonnements',
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'ecole' => $ecole,
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des informations.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mettre à jour les informations de l'école
     */
    public function update(Request $request)
    {
        $user = $request->user();

        if ($user->type !== 'ECOLE') {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé.',
            ], 403);
        }

        $validated = $request->validate([
            'nom' => 'sometimes|string|max:255',
            'nom_complet' => 'sometimes|nullable|string|max:500',
            'email' => 'sometimes|nullable|email|max:255',
            'telephone' => 'sometimes|string|max:20',
            'adresse' => 'sometimes|nullable|string|max:500',
            'responsable_nom' => 'sometimes|string|max:255',
            'responsable_prenom' => 'sometimes|string|max:255',
            'responsable_telephone' => 'sometimes|string|max:20',
        ]);

        try {
            $ecole = $this->ecoleService->update($user->user_account_type_id, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Informations mises à jour avec succès.',
                'data' => [
                    'ecole' => $ecole,
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

<?php

namespace App\Services;

use App\Repositories\Contracts\EcoleRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Contracts\SireneRepositoryInterface;
use App\Services\Contracts\AbonnementServiceInterface;
use App\Services\Contracts\EcoleServiceInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class EcoleService extends BaseService implements EcoleServiceInterface
{
    protected $userRepository;
    protected $sireneRepository;
    protected $abonnementService;

    public function __construct(
        EcoleRepositoryInterface $repository,
        UserRepositoryInterface $userRepository,
        SireneRepositoryInterface $sireneRepository,
        AbonnementServiceInterface $abonnementService
    ) {
        parent::__construct($repository);
        $this->userRepository = $userRepository;
        $this->sireneRepository = $sireneRepository;
        $this->abonnementService = $abonnementService;
    }

    /**
     * Inscription complète d'une école avec sites et affectation de sirènes
     *
     * @param array $ecoleData - Données de l'école (nom, email, telephone, etc.)
     * @param array $sitePrincipalData - Données du site principal avec sirène
     * @param array $sitesAnnexeData - Tableau des sites annexes avec leurs sirènes (optionnel)
     * @return Model
     */
    public function inscrireEcole(array $ecoleData, array $sitePrincipalData, array $sitesAnnexeData = []): Model
    {
        try {
            DB::beginTransaction();

            // 1. Créer l'école
            $ecole = $this->repository->create($ecoleData);

            // 2. Créer le site principal avec sa sirène
            $sitePrincipal = $this->createSiteWithSirene(
                $ecole->id,
                $sitePrincipalData,
                true // est_principale
            );

            // 3. Créer les sites annexes avec leurs sirènes (si fournis)
            if (!empty($sitesAnnexeData)) {
                foreach ($sitesAnnexeData as $siteAnnexeData) {
                    $this->createSiteWithSirene(
                        $ecole->id,
                        $siteAnnexeData,
                        false // est_principale
                    );
                }
            }

            // 4. Créer le compte utilisateur pour l'école
            $motDePasse = Str::random(12); // Générer un mot de passe temporaire

            $userData = [
                'nom_utilisateur' => $ecoleData['nom'],
                'mot_de_passe' => $motDePasse, // Password en clair (sera haché automatiquement dans UserRepository)
                'type' => 'ECOLE',
                'user_account_type_id' => $ecole->id,
                'user_account_type_type' => get_class($ecole),
                'userInfoData' => [
                    'nom' => $ecoleData['nom'],
                    'telephone' => $ecoleData['telephone_contact'],
                    'email' => $ecoleData['email_contact'] ?? null,
                ],
            ];

            $this->userRepository->create($userData);

            DB::commit();

            // Recharger l'école avec toutes les relations
            return $ecole->load([
                'sites.sirene',
                'abonnementActif',
                'user'
            ])->setAttribute('mot_de_passe_temporaire', $motDePasse);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error in " . get_class($this) . "::inscrireEcole - " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Créer un site avec sa sirène affectée
     */
    protected function createSiteWithSirene(string $ecoleId, array $siteData, bool $estPrincipale)
    {
        // Extraire les données de la sirène
        $sireneData = $siteData['sirene'] ?? null;
        unset($siteData['sirene']);

        // Créer le site
        $site = $this->repository->siteRepository->create(array_merge($siteData, [
            'ecole_principale_id' => $ecoleId,
            'est_principale' => $estPrincipale,
        ]));

        // Affecter la sirène au site si fournie
        if ($sireneData && isset($sireneData['numero_serie'])) {
            $sirene = $this->sireneRepository->findByNumeroSerie($sireneData['numero_serie']);

            if (!$sirene) {
                throw new \Exception("Sirène avec numéro de série {$sireneData['numero_serie']} introuvable.");
            }

            if ($sirene->statut !== 'DISPONIBLE' || $sirene->site_id !== null) {
                throw new \Exception("La sirène {$sireneData['numero_serie']} n'est pas disponible.");
            }

            // Affecter la sirène au site
            $this->sireneRepository->affecterSireneASite($sirene->id, $site->id);

            // Créer un abonnement en attente pour l'école
            $abonnement = $this->abonnementService->create([
                'ecole_id' => $ecoleId,
                'site_id' => $site->id, // Link abonnement to the site
                'sirene_id' => $sirene->id, // Link abonnement to the sirene
                'statut' => \App\Enums\StatutAbonnement::EN_ATTENTE->value,
                // Autres champs nécessaires pour l'abonnement, si applicable
                // Par exemple, date_debut, date_fin, etc.
                // Pour l'instant, nous créons un abonnement minimal en attente.
            ]);

            // Générer et sauvegarder le QR code pour l'abonnement
            $qrCodePath = $abonnement->generateAndSaveQrCode();
            $abonnement->update(['qr_code_path' => $qrCodePath]);
        }

        return $site;
    }
}

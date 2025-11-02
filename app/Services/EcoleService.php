<?php

namespace App\Services;

use App\Repositories\Contracts\EcoleRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Contracts\SireneRepositoryInterface;
use App\Services\Contracts\EcoleServiceInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EcoleService extends BaseService implements EcoleServiceInterface
{
    protected $userRepository;
    protected $sireneRepository;

    public function __construct(
        EcoleRepositoryInterface $repository,
        UserRepositoryInterface $userRepository,
        SireneRepositoryInterface $sireneRepository
    ) {
        parent::__construct($repository);
        $this->userRepository = $userRepository;
        $this->sireneRepository = $sireneRepository;
    }

    /**
     * Inscription complète d'une école avec sites et affectation de sirènes
     * 
     * @param array $ecoleData - Données de l'école (nom, email, telephone, etc.)
     * @param array $sitesData - Tableau des sites additionnels (optionnel)
     * @param array $sirenesData - Tableau des numéros de série des sirènes à affecter
     * @return Model
     */
    public function inscrireEcole(array $ecoleData, array $sitesData = [], array $sirenesData = []): Model
    {
        try {
            DB::beginTransaction();

            // 1. Créer l'école avec ses sites (principal + additionnels)
            $ecole = $this->repository->createEcoleWithSites($ecoleData, $sitesData);

            // 2. Affecter les sirènes aux sites
            // Si c'est mono-site, affecter toutes les sirènes au site principal
            // Si c'est multi-sites, répartir selon sirenesData
            if (!empty($sirenesData)) {
                $sites = $ecole->sites;
                
                foreach ($sirenesData as $index => $sireneAffectation) {
                    // Vérifier que la sirène existe et est disponible
                    $sirene = $this->sireneRepository->findByNumeroSerie($sireneAffectation['numero_serie']);
                    
                    if (!$sirene) {
                        throw new \Exception("Sirène avec numéro de série {$sireneAffectation['numero_serie']} introuvable.");
                    }

                    if ($sirene->statut !== 'DISPONIBLE' || $sirene->site_id !== null) {
                        throw new \Exception("La sirène {$sireneAffectation['numero_serie']} n'est pas disponible.");
                    }

                    // Déterminer le site d'affectation
                    $siteId = null;
                    if (isset($sireneAffectation['site_nom'])) {
                        // Rechercher le site par nom
                        $site = $sites->firstWhere('nom', $sireneAffectation['site_nom']);
                        if ($site) {
                            $siteId = $site->id;
                        }
                    } else {
                        // Affecter au site principal par défaut
                        $sitePrincipal = $sites->firstWhere('est_principale', true);
                        $siteId = $sitePrincipal->id;
                    }

                    // Affecter la sirène au site
                    $this->sireneRepository->affecterSireneASite($sirene->id, $siteId);
                }
            }

            // 3. Créer le compte utilisateur pour l'école
            $identifiant = $this->generateIdentifiant($ecoleData['nom']);
            $motDePasse = Str::random(12); // Générer un mot de passe temporaire

            $userData = [
                'nom_utilisateur' => $ecoleData['nom'],
                'identifiant' => $identifiant,
                'mot_de_passe' => Hash::make($motDePasse),
                'type' => 'ECOLE',
                'user_account_type_id' => $ecole->id,
                'user_account_type_type' => get_class($ecole),
            ];

            $userInfoData = [
                'nom' => $ecoleData['nom'],
                'telephone' => $ecoleData['telephone'],
                'email' => $ecoleData['email'] ?? null,
            ];

            $user = $this->userRepository->createWithInfo($userData, $userInfoData);

            DB::commit();

            // Recharger l'école avec toutes les relations
            return $ecole->load([
                'sites.sirenes',
                'abonnementActif',
                'user'
            ])->setAttribute('mot_de_passe_temporaire', $motDePasse);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Error in " . get_class($this) . "::inscrireEcole - " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Générer un identifiant unique pour l'école
     */
    protected function generateIdentifiant(string $nomEcole): string
    {
        $base = Str::slug($nomEcole);
        $identifiant = $base;
        $counter = 1;

        while ($this->userRepository->findBy('identifiant', $identifiant)) {
            $identifiant = $base . '-' . $counter;
            $counter++;
        }

        return $identifiant;
    }
}

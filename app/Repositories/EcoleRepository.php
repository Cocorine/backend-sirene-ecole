<?php

namespace App\Repositories;

use App\Models\Ecole;
use App\Repositories\Contracts\EcoleRepositoryInterface;
use App\Repositories\Contracts\SiteRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Contracts\AbonnementRepositoryInterface;
use App\Repositories\Contracts\ProgrammationRepositoryInterface;
use App\Repositories\Contracts\PanneRepositoryInterface;
use App\Repositories\Contracts\PaiementRepositoryInterface;
use App\Repositories\Contracts\JourFerieRepositoryInterface;
use Illuminate\Support\Facades\DB;

class EcoleRepository extends BaseRepository implements EcoleRepositoryInterface
{
    protected $siteRepository;
    protected $userRepository;
    protected $abonnementRepository;
    protected $programmationRepository;
    protected $panneRepository;
    protected $paiementRepository;
    protected $jourFerieRepository;

    public function __construct(
        Ecole $model,
        SiteRepositoryInterface $siteRepository,
        UserRepositoryInterface $userRepository,
        AbonnementRepositoryInterface $abonnementRepository,
        ProgrammationRepositoryInterface $programmationRepository,
        PanneRepositoryInterface $panneRepository,
        PaiementRepositoryInterface $paiementRepository,
        JourFerieRepositoryInterface $jourFerieRepository
    )
    {
        parent::__construct($model);
        $this->siteRepository = $siteRepository;
        $this->userRepository = $userRepository;
        $this->abonnementRepository = $abonnementRepository;
        $this->programmationRepository = $programmationRepository;
        $this->panneRepository = $panneRepository;
        $this->paiementRepository = $paiementRepository;
        $this->jourFerieRepository = $jourFerieRepository;
    }

    public function delete(string $id): bool
    {
        return DB::transaction(function () use ($id) {
            $ecole = parent::find($id);

            if (!$ecole) {
                return false;
            }

            // Find and delete the associated User account
            $user = $this->userRepository->findByUserAccount(Ecole::class, $ecole->id);
            if ($user) {
                $this->userRepository->delete($user->id);
            }

            // Delete associated Sites and their related entities
            foreach ($ecole->sites as $site) {
                // Delete Abonnements for the site
                foreach ($site->abonnements as $abonnement) {
                    $this->abonnementRepository->delete($abonnement->id);
                }
                // Delete Programmations for the site
                foreach ($site->programmations as $programmation) {
                    $this->programmationRepository->delete($programmation->id);
                }
                // Delete Pannes for the site
                foreach ($site->pannes as $panne) {
                    $this->panneRepository->delete($panne->id);
                }
                // Delete the Site itself
                $this->siteRepository->delete($site->id);
            }

            // Delete Paiements directly linked to the Ecole
            // Assuming Paiement model has a direct relationship to Ecole
            // If not, you might need to adjust this based on how Paiements are linked to Ecole
            // For now, assuming a direct 'paiements' relationship on Ecole model or fetching via repository
            // If Ecole model has a hasMany(Paiement::class) relationship:
            foreach ($ecole->paiements as $paiement) {
                $this->paiementRepository->delete($paiement->id);
            }

            // Delete JoursFeries directly linked to the Ecole
            // If Ecole model has a hasMany(JourFerie::class) relationship:
            foreach ($ecole->joursFeries as $jourFerie) {
                $this->jourFerieRepository->delete($jourFerie->id);
            }

            // Delete the Ecole
            return parent::delete($id);
        });
    }

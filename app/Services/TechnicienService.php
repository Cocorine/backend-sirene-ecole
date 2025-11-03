<?php

namespace App\Services;

use App\Repositories\Contracts\TechnicienRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\Contracts\TechnicienServiceInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TechnicienService extends BaseService implements TechnicienServiceInterface
{
    protected UserRepositoryInterface $userRepository;

    public function __construct(
        TechnicienRepositoryInterface $repository,
        UserRepositoryInterface $userRepository
    ) {
        parent::__construct($repository);
        $this->userRepository = $userRepository;
    }

    public function createTechnicien(array $technicienData): Model
    {
        try {
            DB::beginTransaction();

            // 1. Créer le technicien
            $technicien = $this->repository->create($technicienData);

            // 2. Créer le compte utilisateur pour le technicien
            $motDePasse = Str::random(12); // Générer un mot de passe temporaire

            $userData = [
                'nom_utilisateur' => $technicienData['nom'] . ' ' . $technicienData['prenom'],
                'mot_de_passe' => $motDePasse, // Password en clair (sera haché automatiquement dans UserRepository)
                'type' => 'TECHNICIEN',
                'user_account_type_id' => $technicien->id,
                'user_account_type_type' => get_class($technicien),
                'userInfoData' => [
                    'nom' => $technicienData['nom'],
                    'prenom' => $technicienData['prenom'],
                    'telephone' => $technicienData['telephone'],
                    'email' => $technicienData['email'] ?? null,
                ],
            ];

            $this->userRepository->create($userData);

            DB::commit();

            // Recharger le technicien avec toutes les relations
            return $technicien->load(['user'])->setAttribute('mot_de_passe_temporaire', $motDePasse);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error in " . get_class($this) . "::createTechnicien - " . $e->getMessage());
            throw $e;
        }
    }
}
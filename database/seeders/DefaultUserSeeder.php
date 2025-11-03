<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\UserInfo;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Database\Seeder;

class DefaultUserSeeder extends Seeder
{
    protected UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get roles
        $adminRole = Role::where('slug', 'admin')->first();
        $userRole = Role::where('slug', 'user')->first();
        $ecoleRole = Role::where('slug', 'ecole')->first();
        $technicienRole = Role::where('slug', 'technicien')->first();

        UserInfo::all()->each->forceDelete();
        User::all()->each->forceDelete();

        // Create Admin User
        if ($adminRole) {
            $adminData = [
                'mot_de_passe' => 'password',
                'type' => 'ADMIN',
                'role_id' => $adminRole->id,
                'userInfoData' => [
                    'email' => 'admin@example.com',
                    'telephone' => '2290162004867',
                    'prenom' => 'Admin',
                    'nom' => 'User',
                ],
                'doit_changer_mot_de_passe' => false,
                'mot_de_passe_change' => true,
                'actif' => true,
                'statut' => 1,
            ];
            // Check if user already exists by email (identifiant)
            if (!$this->userRepository->findByEmail('admin@example.com')) {
                $this->userRepository->create($adminData);
            }
        }

        // Create Regular User
        if ($userRole) {
            $userData = [
                'mot_de_passe' => 'password',
                'type' => 'USER',
                'role_id' => $userRole->id,
                'userInfoData' => [
                    'email' => 'user@example.com',
                    'telephone' => '22962004867',
                    'prenom' => 'Regular',
                    'nom' => 'User',
                ],
            ];
            // Check if user already exists by email (identifiant)
            if (!$this->userRepository->findByEmail('user@example.com')) {
                $this->userRepository->create($userData);
            }
        }

        // Create Ecole User (example)
        if ($ecoleRole) {
            $ecoleUserData = [
                'mot_de_passe' => 'password',
                'type' => 'ECOLE',
                'role_id' => $ecoleRole->id,
                'userInfoData' => [
                    'email' => 'ecole@example.com',
                    'telephone' => '2290167217812',
                    'nom' => 'Ecole Test',
                ],
            ];
            if (!$this->userRepository->findByEmail('ecole@example.com')) {
                $this->userRepository->create($ecoleUserData);
            }
        }

        // Create Technicien User (example)
        if ($technicienRole) {
            $technicienUserData = [
                'mot_de_passe' => 'password',
                'type' => 'TECHNICIEN',
                'role_id' => $technicienRole->id,
                'userInfoData' => [
                    'email' => 'technicien@example.com',
                    'telephone' => '01554433221',
                    'prenom' => 'Tech',
                    'nom' => 'User',
                ],
            ];
            if (!$this->userRepository->findByEmail('technicien@example.com')) {
                $this->userRepository->create($technicienUserData);
            }
        }
    }
}

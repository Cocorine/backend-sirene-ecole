<?php

namespace App\Repositories;

use App\Enums\TypeUtilisateur;
use App\Models\Role;
use App\Models\Technicien;
use App\Repositories\Contracts\TechnicienRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TechnicienRepository extends BaseRepository implements TechnicienRepositoryInterface
{
    protected UserRepositoryInterface $userRepository;

    public function __construct(Technicien $model, UserRepositoryInterface $userRepository)
    {
        parent::__construct($model);
        $this->userRepository = $userRepository;
    }

    public function getAvailableTechniciens()
    {
        return $this->model->where('disponibilite', true)->get();
    }

    public function getTechniciensBySpecialite(string $specialite)
    {
        return $this->model->where('specialite', $specialite)->get();
    }



    public function create(array $data): Model
    {
        try {
            DB::beginTransaction();

            // Assuming $data contains 'user' key for user data and other keys for technician data
            $userData = $data['user'] ?? [];

            $technicienData = array_diff_key($data, array_flip(['user'])); // Remove 'user' key from technician data

            // 1. Create the Technicien first
            $technicien = parent::create($technicienData);

            if (!$technicien) {
                throw new \Exception('Failed to create technician.');
            }

            // 2. Prepare user data with polymorphic relationship details
            $userData['user_account_type_id'] = $technicien->id;
            $userData['user_account_type_type'] = Technicien::class;
            $userData['type'] = TypeUtilisateur::TECHNICIEN;
            $userData['role_id'] = Role::where('slug', 'technicien')->first()->id;

            // 3. Create the User associated with the Technicien
            $user = $this->userRepository->create($userData);

            if (!$user) {
                throw new \Exception('Failed to create user for technician.');
            }

            DB::commit();

            // Return the technician with its associated user account (which is the User model)
            return $technicien->load('user.userAccount');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error in " . get_class($this) . "::create - " . $e->getMessage());
            throw $e;
        }
    }

    public function update(string $id, array $data): bool
    {
        try {
            DB::beginTransaction();

            $technicien = parent::find($id);
            if (!$technicien) {
                return false;
            }

            $userData = $data['user'] ?? [];
            $technicienData = array_diff_key($data, array_flip(['user']));

            // Update Technicien data
            $updatedTechnicien = parent::update($id, $technicienData);

            if (!$updatedTechnicien) {
                DB::rollBack();
                return false;
            }

            // Update associated User data
            $user = $technicien->userAccount;
            if ($user && !empty($userData)) {
                $this->userRepository->update($user->id, $userData);
            }

            DB::commit();

            return $updatedTechnicien;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error in " . get_class($this) . "::update - " . $e->getMessage());
            throw $e;
        }
    }

    public function delete(string $id): bool
    {
        try {
            DB::beginTransaction();

            $technicien = parent::find($id);
            if (!$technicien) {
                return false;
            }

            // Delete associated User data
            $user = $technicien->userAccount;
            if ($user) {
                $this->userRepository->delete($user->id);
            }

            // Delete Technicien data
            $deleteResult = parent::delete($id);

            if (!$deleteResult) {
                DB::rollBack();
                return false;
            }

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error in " . get_class($this) . "::delete - " . $e->getMessage());
            throw $e;
        }
    }

    // Add any specific methods for TechnicienRepository here
}

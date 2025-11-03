<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Model;

interface TechnicienRepositoryInterface extends BaseRepositoryInterface
{
    public function getAvailableTechniciens();

    public function delete(string $id): bool;

    // Add any specific methods for TechnicienRepository here
}

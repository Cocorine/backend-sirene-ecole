<?php

namespace App\Services\Contracts;

use Illuminate\Database\Eloquent\Model;

interface TechnicienServiceInterface extends BaseServiceInterface
{
    public function createTechnicien(array $technicienData): Model;
}
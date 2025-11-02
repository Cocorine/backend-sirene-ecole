<?php

namespace App\Services\Contracts;

interface SireneServiceInterface extends BaseServiceInterface
{
    public function findByNumeroSerie(string $numeroSerie);
    public function getSirenesDisponibles();
    public function affecterSireneASite(string $sireneId, string $siteId);
}

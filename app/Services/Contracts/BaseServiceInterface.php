<?php

namespace App\Services\Contracts;

use Illuminate\Http\JsonResponse;

interface BaseServiceInterface
{
    /**
     * Récupérer tous les enregistrements paginés
     */
    public function getAll(int $perPage = 15, array $relations = []): JsonResponse;

    /**
     * Récupérer un enregistrement par ID
     */
    public function getById(string $id, array $columns = ['*'], array $relations = []): JsonResponse;

    /**
     * Créer un nouvel enregistrement
     */
    public function create(array $data): JsonResponse;

    /**
     * Mettre à jour un enregistrement
     */
    public function update(string $id, array $data): JsonResponse;

    /**
     * Supprimer un enregistrement
     */
    public function delete(string $id): JsonResponse;

    /**
     * Supprimer définitivement un enregistrement (force delete)
     */
    public function forceDelete(string $id): JsonResponse;

    /**
     * Restaurer un enregistrement supprimé
     */
    public function restore(string $id): JsonResponse;

    /**
     * Rechercher par critères
     */
    public function findBy(array $criteria, array $relations = []): JsonResponse;

    /**
     * Récupérer plusieurs enregistrements par critères
     */
    public function findAllBy(array $criteria, array $relations = []): JsonResponse;

    /**
     * Vérifier si un enregistrement existe
     */
    public function exists(array $criteria): JsonResponse;

    /**
     * Compter les enregistrements
     */
    public function count(array $criteria = []): JsonResponse;
}

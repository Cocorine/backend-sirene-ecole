<?php

namespace App\Services;

use App\Repositories\Contracts\AbonnementRepositoryInterface;
use App\Services\Contracts\AbonnementServiceInterface;

class AbonnementService extends BaseService implements AbonnementServiceInterface
{
    protected AbonnementRepositoryInterface $abonnementRepository;
    protected TokenEncryptionService $tokenService;

    public function __construct(
        AbonnementRepositoryInterface $abonnementRepository,
        TokenEncryptionService $tokenService
    ) {
        $this->abonnementRepository = $abonnementRepository;
        $this->tokenService = $tokenService;
    }

    /*public function createAbonnement(array $data): Abonnement
    {
        $data['numero_abonnement'] = $this->generateNumeroAbonnement();
        $data['date_debut'] = $data['date_debut'] ?? now();
        $data['date_fin'] = $data['date_fin'] ?? now()->addYear();
        $data['montant'] = $data['montant'] ?? config('services.subscription.price_per_year');
        $data['statut'] = $data['statut'] ?? 'en_attente';

        return $this->abonnementRepository->create($data);
    }

    public function getActiveAbonnement(string $ecoleId): ?Abonnement
    {
        return $this->abonnementRepository->getActiveByEcole($ecoleId);
    }

    public function renewAbonnement(string $abonnementId, array $data): Abonnement
    {
        $data['numero_abonnement'] = $this->generateNumeroAbonnement();
        $data['date_debut'] = now();
        $data['date_fin'] = now()->addYear();
        $data['statut'] = 'en_attente';

        return $this->abonnementRepository->renew($abonnementId, $data);
    }

    public function generateToken(string $abonnementId): Abonnement
    {
        $abonnement = $this->abonnementRepository->find($abonnementId, ['*'], ['ecole']);

        if (!$abonnement) {
            throw new \Exception("Abonnement non trouvé");
        }

        $this->tokenService->generateToken($abonnement);
        return $abonnement->fresh(['token']);
    }

    public function activateAfterPayment(string $abonnementId): Abonnement
    {
        $abonnement = $this->abonnementRepository->find($abonnementId);

        if (!$abonnement) {
            throw new \Exception("Abonnement non trouvé");
        }

        $this->abonnementRepository->update($abonnementId, [
            'statut' => 'actif',
            'date_paiement' => now(),
        ]);

        $this->generateToken($abonnementId);
        return $abonnement->fresh(['token', 'ecole']);
    }

    public function getExpiringSoon(int $days = 30): Collection
    {
        return $this->abonnementRepository->getExpiringSoon($days);
    }

    public function markExpiredSubscriptions(): int
    {
        $expiredAbonnements = $this->abonnementRepository->getExpired();
        $count = 0;

        foreach ($expiredAbonnements as $abonnement) {
            $this->abonnementRepository->update($abonnement->id, ['statut' => 'expire']);

            if ($abonnement->token) {
                $this->tokenService->deactivateToken($abonnement->token);
            }
            $count++;
        }

        return $count;
    }

    private function generateNumeroAbonnement(): string
    {
        do {
            $numero = 'ABO' . date('Ymd') . strtoupper(Str::random(6));
        } while ($this->abonnementRepository->exists(['numero_abonnement' => $numero]));

        return $numero;
    }*/
}

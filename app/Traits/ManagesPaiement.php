<?php

namespace App\Traits;

use App\Models\Paiement;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

trait ManagesPaiement
{
    /**
     * Boot the trait for payment event listeners
     */
    protected static function bootManagesPaiement(): void
    {
        // Listen to Paiement model events if this is attached to Abonnement
        static::created(function (Model $model) {
            // Fire event when abonnement is created
            event('abonnement.created', [$model]);
        });

        static::updated(function (Model $model) {
            // Check if statut changed to ACTIF
            if ($model->isDirty('statut') && $model->statut === 'ACTIF') {
                event('abonnement.activated', [$model]);
            }
        });
    }

    /**
     * Create payment for this subscription
     */
    public function createPaiement(array $data = []): Paiement
    {
        return Paiement::create(array_merge([
            'abonnement_id' => $this->id,
            'montant' => $this->montant,
            'statut' => 'EN_ATTENTE',
            'date_paiement' => Carbon::now(),
        ], $data));
    }

    /**
     * Mark payment as successful
     */
    public function markPaiementAsSuccessful(Paiement $paiement, array $transactionData = []): void
    {
        $paiement->update([
            'statut' => 'REUSSI',
            'date_validation' => Carbon::now(),
            'reference_transaction' => $transactionData['reference'] ?? null,
            'mode_paiement' => $transactionData['mode'] ?? null,
        ]);

        // Activate subscription
        $this->update(['statut' => 'ACTIF']);

        // Fire payment success event
        event('paiement.successful', [$paiement, $this]);
    }

    /**
     * Mark payment as failed
     */
    public function markPaiementAsFailed(Paiement $paiement, string $reason = null): void
    {
        $paiement->update([
            'statut' => 'ECHOUE',
            'motif_echec' => $reason,
        ]);

        // Fire payment failed event
        event('paiement.failed', [$paiement, $this]);
    }

    /**
     * Get all payments for this subscription
     */
    public function paiements()
    {
        return $this->hasMany(Paiement::class, 'abonnement_id')->latest('date_paiement');
    }

    /**
     * Get successful payment
     */
    public function paiementReussi()
    {
        return $this->hasOne(Paiement::class, 'abonnement_id')
            ->where('statut', 'REUSSI')
            ->latest('date_validation');
    }

    /**
     * Check if subscription is paid
     */
    public function isPaid(): bool
    {
        return $this->paiementReussi()->exists();
    }

    /**
     * Get pending payments
     */
    public function paiementsEnAttente()
    {
        return $this->hasMany(Paiement::class, 'abonnement_id')
            ->where('statut', 'EN_ATTENTE');
    }

    /**
     * Initiate payment with aggregator (to be implemented)
     */
    public function initiatePayment(string $method = 'mobile_money', array $customerData = []): array
    {
        // Create payment record
        $paiement = $this->createPaiement([
            'mode_paiement' => $method,
        ]);

        // Fire payment initiated event
        event('paiement.initiated', [$paiement, $this, $customerData]);

        // TODO: Integrate with payment aggregator (FedaPay, CinetPay, etc.)
        // For now, return payment details
        return [
            'paiement_id' => $paiement->id,
            'montant' => $paiement->montant,
            'statut' => $paiement->statut,
            'abonnement_id' => $this->id,
            'message' => 'Paiement initié. En attente de l\'intégration de l\'agrégateur.',
        ];
    }

    /**
     * Handle payment webhook callback
     */
    public function handlePaymentCallback(array $callbackData): void
    {
        $paiement = Paiement::find($callbackData['paiement_id'] ?? null);
        
        if (!$paiement) {
            return;
        }

        if ($callbackData['status'] === 'success') {
            $this->markPaiementAsSuccessful($paiement, [
                'reference' => $callbackData['transaction_id'] ?? null,
                'mode' => $callbackData['payment_method'] ?? null,
            ]);
        } else {
            $this->markPaiementAsFailed($paiement, $callbackData['error_message'] ?? 'Paiement échoué');
        }
    }
}

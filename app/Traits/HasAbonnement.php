<?php

namespace App\Traits;

use App\Models\Abonnement;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

trait HasAbonnement
{
    /**
     * Boot the trait and automatically generate abonnement on Ecole creation
     */
    protected static function bootHasAbonnement(): void
    {
        static::created(function (Model $model) {
            if (!$model->abonnementActif) {
                self::createAnnualAbonnement($model);
            }
        });
    }

    /**
     * Create annual subscription for the school
     */
    protected static function createAnnualAbonnement(Model $model): Abonnement
    {
        $dateDebut = Carbon::now();
        $dateFin = Carbon::now()->addYear();

        return Abonnement::create([
            'ecole_id' => $model->id,
            'date_debut' => $dateDebut,
            'date_fin' => $dateFin,
            'montant' => config('abonnement.montant_annuel', 50000), // Montant par défaut
            'statut' => 'EN_ATTENTE', // En attente de paiement
            'type' => 'ANNUEL',
            'nombre_sirenes_autorisees' => config('abonnement.sirenes_default', 1),
        ]);
    }

    /**
     * Get active subscription
     */
    public function abonnementActif()
    {
        return $this->hasOne(Abonnement::class, 'ecole_id')
            ->where('statut', 'ACTIF')
            ->where('date_fin', '>=', Carbon::now())
            ->latest('date_debut');
    }

    /**
     * Get all subscriptions
     */
    public function abonnements()
    {
        return $this->hasMany(Abonnement::class, 'ecole_id')->latest('date_debut');
    }

    /**
     * Check if subscription is active
     */
    public function hasActiveAbonnement(): bool
    {
        return $this->abonnementActif()->exists();
    }

    /**
     * Renew subscription for another year
     */
    public function renewAbonnement(): Abonnement
    {
        $lastAbonnement = $this->abonnements()->first();
        $dateDebut = $lastAbonnement ? Carbon::parse($lastAbonnement->date_fin)->addDay() : Carbon::now();
        $dateFin = $dateDebut->copy()->addYear();

        return Abonnement::create([
            'ecole_id' => $this->id,
            'date_debut' => $dateDebut,
            'date_fin' => $dateFin,
            'montant' => config('abonnement.montant_annuel', 50000),
            'statut' => 'EN_ATTENTE',
            'type' => 'ANNUEL',
            'nombre_sirenes_autorisees' => config('abonnement.sirenes_default', 1),
        ]);
    }
}

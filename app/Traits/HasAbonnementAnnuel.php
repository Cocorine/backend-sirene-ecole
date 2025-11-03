<?php

namespace App\Traits;

use App\Models\Abonnement;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

trait HasAbonnementAnnuel
{
    protected static function bootHasAbonnementAnnuel(): void
    {
        static::updated(function (Model $model) {
            // Génère l'abonnement annuel quand la sirène est affectée à un site
            if ($model->isDirty('site_id') && $model->site_id && $model->ecole_id) {
                // Vérifie si un abonnement actif n'existe pas déjà pour cette sirène
                $abonnementActif = $model->abonnements()
                    ->where('statut', 'actif')
                    ->where('date_fin', '>=', now())
                    ->exists();

                if (!$abonnementActif) {
                    self::genererAbonnementAnnuel($model);
                }
            }
        });
    }

    protected static function genererAbonnementAnnuel(Model $model): void
    {
        $dateDebut = Carbon::now();
        $dateFin = Carbon::now()->addYear();

        // Calcul du montant annuel (peut être personnalisé selon le type de sirène)
        $montantAnnuel = self::calculerMontantAnnuel($model);

        // Générer le numéro d'abonnement
        $numeroAbonnement = self::generateNumeroAbonnement();

        Abonnement::create([
            'sirene_id' => $model->id,
            'ecole_id' => $model->ecole_id,
            'site_id' => $model->site_id,
            'numero_abonnement' => $numeroAbonnement,
            'date_debut' => $dateDebut,
            'date_fin' => $dateFin,
            'montant' => $montantAnnuel,
            'statut' => 'en_attente', // En attente de paiement
            'auto_renouvellement' => false,
        ]);
    }

    protected static function generateNumeroAbonnement(): string
    {
        do {
            $numero = 'ABO-' . date('Ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(6));
        } while (Abonnement::where('numero_abonnement', $numero)->exists());

        return $numero;
    }

    protected static function calculerMontantAnnuel(Model $model): float
    {
        // Logique de calcul du montant selon le type de sirène ou autres critères
        // À personnaliser selon les besoins métier
        $tarifBase = 50000; // Montant en centimes (500.00)

        // Exemple: variation selon le type de sirène
        if ($model->type_sirene === 'premium') {
            return $tarifBase * 1.5;
        }

        return $tarifBase;
    }
}

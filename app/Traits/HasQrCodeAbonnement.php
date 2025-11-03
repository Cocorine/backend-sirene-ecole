<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;

trait HasQrCodeAbonnement
{
    protected static function bootHasQrCodeAbonnement(): void
    {
        static::created(function (Model $model) {
            // Génère le QR code après la création de l'abonnement
            self::genererQrCode($model);
        });

        static::updated(function (Model $model) {
            // Régénère le QR code si le statut change
            if ($model->isDirty('statut')) {
                self::genererQrCode($model);
            }
        });
    }

    protected static function genererQrCode(Model $model): void
    {
        // Charger les relations nécessaires
        $model->load(['sirene.ecole', 'ecole']);

        $ecole = $model->ecole ?? $model->sirene?->ecole;
        
        if (!$ecole) {
            return; // Pas d'école, pas de QR code
        }

        // URL de paiement pour le QR code
        // Si l'abonnement est déjà actif, on affiche les détails
        // Sinon on redirige vers la page de paiement
        if ($model->statut->value === 'actif') {
            $url = config('app.url') . '/api/abonnements/' . $model->id . '/details';
        } else {
            $url = config('app.url') . '/api/abonnements/' . $model->id . '/paiement';
        }

        // Le QR code contient directement l'URL
        // L'utilisateur scannera et sera redirigé vers la page appropriée
        $qrContent = $url;

        // Générer le QR code en PNG
        $qrCode = QrCode::format('png')
            ->size(300)
            ->errorCorrection('H')
            ->generate($qrContent);

        // Sauvegarder le QR code
        $filename = 'qrcodes/abonnement_' . $model->id . '.png';
        Storage::disk('public')->put($filename, $qrCode);

        // Mettre à jour le modèle avec le chemin du QR code
        $model->qr_code_path = $filename;
        $model->saveQuietly(); // Évite de déclencher les events à nouveau
    }

    public function getQrCodeUrl(): ?string
    {
        if (!$this->qr_code_path) {
            return null;
        }

        return Storage::disk('public')->url($this->qr_code_path);
    }

    public function regenererQrCode(): void
    {
        self::genererQrCode($this);
    }
}

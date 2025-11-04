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

        // URL frontend - Scan ouvre le navigateur
        $frontendUrl = config('app.frontend_url', config('app.url'));
        $qrContent = $model->statut->value === 'actif'
            ? $frontendUrl . '/abonnements/' . $model->id
            : $frontendUrl . '/paiement/' . $model->id;

        // Générer le QR code en PNG
        $qrCode = QrCode::format('png')
            ->size(300)
            ->errorCorrection('H')
            ->generate($qrContent);

        // Sauvegarder le QR code
        $filename = "ecoles/{$ecole->id}/qrcodes/{$model->sirene->id}/abonnement_" . $model->id . '.png';
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

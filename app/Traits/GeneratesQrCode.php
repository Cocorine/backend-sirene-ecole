<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

trait GeneratesQrCode
{
    /**
     * Generate QR Code for the model
     */
    public function generateQrCode(array $additionalData = []): string
    {
        $qrData = $this->getQrCodeData($additionalData);
        $qrCode = QrCode::format('png')
            ->size(300)
            ->margin(1)
            ->errorCorrection('H')
            ->generate(json_encode($qrData));

        return base64_encode($qrCode);
    }

    /**
     * Generate and save QR Code to storage
     */
    public function generateAndSaveQrCode(array $additionalData = []): string
    {
        $qrData = $this->getQrCodeData($additionalData);
        $fileName = $this->getQrCodeFileName();
        $path = "qrcodes/{$fileName}";

        $qrCode = QrCode::format('png')
            ->size(300)
            ->margin(1)
            ->errorCorrection('H')
            ->generate(json_encode($qrData));

        Storage::disk('public')->put($path, $qrCode);

        return $path;
    }

    /**
     * Get QR Code data to encode
     */
    protected function getQrCodeData(array $additionalData = []): array
    {
        $baseData = [
            'model_type' => get_class($this),
            'model_id' => $this->id,
            'generated_at' => now()->toIso8601String(),
        ];

        // For Ecole model
        if (method_exists($this, 'abonnementActif')) {
            $abonnement = $this->abonnementActif;
            if ($abonnement) {
                $baseData['ecole'] = [
                    'id' => $this->id,
                    'nom' => $this->nom ?? null,
                    'email' => $this->email ?? null,
                ];
                $baseData['abonnement'] = [
                    'id' => $abonnement->id,
                    'date_debut' => $abonnement->date_debut,
                    'date_fin' => $abonnement->date_fin,
                    'statut' => $abonnement->statut,
                ];
            }
        }

        // For Sirene model
        if (isset($this->numero_serie)) {
            $baseData['sirene'] = [
                'id' => $this->id,
                'numero_serie' => $this->numero_serie,
            ];
        }

        return array_merge($baseData, $additionalData);
    }

    /**
     * Get QR Code file name
     */
    protected function getQrCodeFileName(): string
    {
        $modelName = class_basename(get_class($this));
        $identifier = $this->numero_serie ?? $this->id;
        return strtolower($modelName) . '_' . $identifier . '_' . time() . '.png';
    }

    /**
     * Get QR Code URL
     */
    public function getQrCodeUrl(): ?string
    {
        if (isset($this->qr_code_path) && $this->qr_code_path) {
            return Storage::disk('public')->url($this->qr_code_path);
        }

        return null;
    }
}

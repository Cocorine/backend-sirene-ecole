<?php

namespace App\Services;

use App\Repositories\Contracts\OtpCodeRepositoryInterface;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;

class OtpService
{
    private int $expirationMinutes;
    private int $maxAttempts;
    private SmsService $smsService;
    private OtpCodeRepositoryInterface $otpCodeRepository;

    public function __construct(SmsService $smsService, OtpCodeRepositoryInterface $otpCodeRepository)
    {
        $this->smsService = $smsService;
        $this->otpCodeRepository = $otpCodeRepository;
        $this->expirationMinutes = config('services.otp.expiration_minutes', 5);
        $this->maxAttempts = config('services.otp.max_attempts', 3);
    }

    /**
     * Génère et envoie un code OTP
     */
    public function generate(string $telephone, string $userId, string $type = 'login'): array
    {
        // Supprimer les anciens codes non vérifiés pour ce numéro
        $this->otpCodeRepository->deleteUnverifiedByPhone($telephone);

        // Générer un code à 6 chiffres
        $code = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

        // Créer le code OTP en base
        $otpCode = $this->otpCodeRepository->create([
            'userId' => $userId,
            'telephone' => $telephone,
            'code' => $code,
            'type' => $type,
            'verifie' => false,
            'date_expiration' => Carbon::now()->addMinutes($this->expirationMinutes),
            'tentatives' => 0,
        ]);

        // Envoyer le SMS
        try {
            $message = "Votre code de vérification Sirène d'École est: {$code}. Valide pendant {$this->expirationMinutes} minutes.";
            $this->smsService->sendSms($telephone, $message);

            return [
                'success' => true,
                'message' => 'Code OTP envoyé avec succès',
                'expires_in' => $this->expirationMinutes,
            ];
        } catch (Exception $e) {
            Log::error('Failed to send OTP: ' . $e->getMessage());
            $this->otpCodeRepository->delete($otpCode->id);

            return [
                'success' => false,
                'message' => 'Échec de l\'envoi du code OTP',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Vérifie un code OTP
     */
    public function verify(string $telephone, string $code): array
    {
        $otpCode = $this->otpCodeRepository->findByTelephoneAndCode($telephone, $code);

        if (!$otpCode) {
            return [
                'success' => false,
                'message' => 'Code OTP invalide',
            ];
        }

        // Vérifier l'expiration
        if (Carbon::now()->isAfter($otpCode->date_expiration)) {
            $this->otpCodeRepository->delete($otpCode->id);
            return [
                'success' => false,
                'message' => 'Code OTP expiré',
            ];
        }

        // Vérifier le nombre de tentatives
        if ($otpCode->tentatives >= $this->maxAttempts) {
            $this->otpCodeRepository->delete($otpCode->id);
            return [
                'success' => false,
                'message' => 'Nombre maximum de tentatives atteint',
            ];
        }

        // Incrémenter les tentatives
        $this->otpCodeRepository->incrementAttempts($otpCode->id);

        // Marquer comme vérifié
        $this->otpCodeRepository->markAsVerified($otpCode->id);

        return [
            'success' => true,
            'message' => 'Code OTP vérifié avec succès',
        ];
    }

    /**
     * Nettoie les codes OTP expirés
     */
    public function cleanupExpired(): int
    {
        return $this->otpCodeRepository->deleteExpired();
    }
}

<?php

namespace App\Services;

use App\Enums\TypeOtp;
use App\Repositories\Contracts\OtpCodeRepositoryInterface;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;

class OtpService
{
    private SmsService $smsService;
    private OtpCodeRepositoryInterface $otpCodeRepository;

    public function __construct(SmsService $smsService, OtpCodeRepositoryInterface $otpCodeRepository)
    {
        $this->smsService = $smsService;
        $this->otpCodeRepository = $otpCodeRepository;
    }

    /**
     * Génère et envoie un code OTP
     */
    public function generate(string $telephone, string $userId, TypeOtp $type = TypeOtp::LOGIN): array
    {
        // Supprimer les anciens codes non vérifiés pour ce numéro et ce type
        $this->otpCodeRepository->deleteUnverifiedByPhone($telephone);

        // Générer un code à 6 chiffres
        $code = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

        // Récupérer les paramètres depuis l'enum
        $expirationMinutes = $type->expiration();
        $maxAttempts = $type->maxAttempts();

        // Créer le code OTP en base
        $otpCode = $this->otpCodeRepository->create([
            'user_id' => $userId,
            'telephone' => $telephone,
            'code' => $code,
            'type' => $type->value,
            'verifie' => false,
            'date_expiration' => Carbon::now()->addMinutes($expirationMinutes),
            'est_verifie' => false,
            'expire_le' => Carbon::now()->addMinutes($expirationMinutes),
            'tentatives' => 0,
        ]);

        // Envoyer le SMS
        try {
            $message = "Votre code de vérification Sirène d'École ({$type->label()}) est: {$code}. Valide pendant {$expirationMinutes} minutes.";
            $this->smsService->sendSms($telephone, $message);

            return [
                'success' => true,
                'message' => 'Code OTP envoyé avec succès',
                'expires_in' => $expirationMinutes,
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
    public function verify(string $telephone, string $code, TypeOtp $type = TypeOtp::LOGIN): array
    {
        $otpCode = $this->otpCodeRepository->findByTelephoneAndCode($telephone, $code);

        if (!$otpCode) {
            return [
                'success' => false,
                'message' => 'Code OTP invalide',
            ];
        }

        // Vérifier que le type correspond
        if ($otpCode->type->value !== $type->value) {
            return [
                'success' => false,
                'message' => 'Code OTP invalide pour cette opération',
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
        $maxAttempts = $type->maxAttempts();
        if ($otpCode->tentatives >= $maxAttempts) {
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

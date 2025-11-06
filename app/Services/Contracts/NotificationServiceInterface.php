<?php

namespace App\Services\Contracts;

use App\Services\BaseService;

interface NotificationServiceInterface
{
    public function sendAdminPaymentNotification(array $paymentDetails): void;
    public function sendNewOrdreMissionNotificationToTechnicians(string $villeId, array $ordreMissionDetails): void;
    public function sendCandidatureValidationNotification(string $technicienId, array $candidatureDetails): void;
    public function sendAdminCandidatureSubmissionNotification(array $candidatureDetails): void;
    public function sendMissionCompletionNotificationToEcole(string $ecoleId, array $missionDetails): void;
}

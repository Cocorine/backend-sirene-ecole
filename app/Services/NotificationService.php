use App\Enums\CanalNotification;
use App\Enums\TypeNotification;
use App\Models\Notification;
use App\Models\User;
use App\Models\Technicien;
use App\Models\Ecole;
use App\Repositories\Contracts\TechnicienRepositoryInterface;
use App\Repositories\Contracts\EcoleRepositoryInterface;
use Exception;
use Illuminate\Support\Facades\Log;

class NotificationService implements NotificationServiceInterface
{
    protected Notification $notificationModel;
    protected TechnicienRepositoryInterface $technicienRepository;
    protected EcoleRepositoryInterface $ecoleRepository;

    public function __construct(Notification $notificationModel, TechnicienRepositoryInterface $technicienRepository, EcoleRepositoryInterface $ecoleRepository)
    {
        $this->notificationModel = $notificationModel;
        $this->technicienRepository = $technicienRepository;
        $this->ecoleRepository = $ecoleRepository;
    }

    /**
     * Send a notification to admin users about a validated payment.
     *
     * @param array $paymentDetails
     * @return void
     */
    public function sendAdminPaymentNotification(array $paymentDetails): void
    {
        try {
            // TODO: Implement logic to retrieve actual admin users.
            // For now, let's assume we have a method to get them.
            $adminUsers = $this->getAdminUsers();

            foreach ($adminUsers as $admin) {
                $this->notificationModel->create([
                    'notifiable_id' => $admin->id,
                    'notifiable_type' => User::class,
                    'type' => TypeNotification::SYSTEME,
                    'canal' => CanalNotification::SYSTEME,
                    'message' => "Un paiement de {$paymentDetails['montant']} a été validé pour l'abonnement {$paymentDetails['abonnement_id']}.",
                    'titre' => 'Nouveau paiement validé',
                    'data' => $paymentDetails,
                    'statut' => false, // Not yet read
                    'date_envoi' => now(),
                ]);
            }
        } catch (Exception $e) {
            Log::error("Error sending admin payment notification: " . $e->getMessage());
        }
    }

    /**
     * Send a notification to technicians in a specific city about a new OrdreMission.
     *
     * @param string $villeId
     * @param array $ordreMissionDetails
     * @return void
     */
    public function sendNewOrdreMissionNotificationToTechnicians(string $villeId, array $ordreMissionDetails): void
    {
        try {
            $technicians = $this->technicienRepository->findAllBy(['ville_id' => $villeId]);

            foreach ($technicians as $technician) {
                $this->notificationModel->create([
                    'notifiable_id' => $technician->id,
                    'notifiable_type' => Technicien::class,
                    'type' => TypeNotification::SYSTEME,
                    'canal' => CanalNotification::SYSTEME,
                    'message' => "Un nouvel ordre de mission ({$ordreMissionDetails['numero_ordre']}) a été généré dans votre ville ({$ordreMissionDetails['ville_nom']}).",
                    'titre' => 'Nouvel Ordre de Mission Disponible',
                    'data' => $ordreMissionDetails,
                    'statut' => false,
                    'date_envoi' => now(),
                ]);
            }
        } catch (Exception $e) {
            Log::error("Error sending new ordre mission notification to technicians: " . $e->getMessage());
        }
    }

    public function sendCandidatureValidationNotification(string $technicienId, array $candidatureDetails): void
    {
        try {
            $technician = $this->technicienRepository->find($technicienId);

            if ($technician) {
                $this->notificationModel->create([
                    'notifiable_id' => $technician->id,
                    'notifiable_type' => Technicien::class,
                    'type' => TypeNotification::SYSTEME,
                    'canal' => CanalNotification::SYSTEME,
                    'message' => "Votre candidature pour l'ordre de mission {$candidatureDetails['numero_ordre']} a été validée.",
                    'titre' => 'Candidature Validée',
                    'data' => $candidatureDetails,
                    'statut' => false,
                    'date_envoi' => now(),
                ]);
            }
        } catch (Exception $e) {
            Log::error("Error sending candidature validation notification: " . $e->getMessage());
        }
    }

    /**
     * Send a notification to admin users about a new candidature submission.
     *
     * @param array $candidatureDetails
     * @return void
     */
    public function sendAdminCandidatureSubmissionNotification(array $candidatureDetails): void
    {
        try {
            $adminUsers = $this->getAdminUsers();

            foreach ($adminUsers as $admin) {
                $this->notificationModel->create([
                    'notifiable_id' => $admin->id,
                    'notifiable_type' => User::class,
                    'type' => TypeNotification::SYSTEME,
                    'canal' => CanalNotification::SYSTEME,
                    'message' => "Une nouvelle candidature a été soumise par le technicien {$candidatureDetails['technicien_nom']} pour l'ordre de mission {$candidatureDetails['numero_ordre']}.",
                    'titre' => 'Nouvelle Candidature Soumise',
                    'data' => $candidatureDetails,
                    'statut' => false,
                    'date_envoi' => now(),
                ]);
            }
        } catch (Exception $e) {
            Log::error("Error sending admin candidature submission notification: " . $e->getMessage());
        }
    }

    /**
     * Send a notification to an Ecole about mission completion.
     *
     * @param string $ecoleId
     * @param array $missionDetails
     * @return void
     */
    public function sendMissionCompletionNotificationToEcole(string $ecoleId, array $missionDetails): void
    {
        try {
            $ecole = $this->ecoleRepository->find($ecoleId);

            if ($ecole) {
                $this->notificationModel->create([
                    'notifiable_id' => $ecole->id,
                    'notifiable_type' => Ecole::class,
                    'type' => TypeNotification::SYSTEME,
                    'canal' => CanalNotification::SYSTEME,
                    'message' => "La mission {$missionDetails['numero_ordre']} est terminée. Veuillez laisser votre avis.",
                    'titre' => 'Mission Terminée - Votre Avis',
                    'data' => $missionDetails,
                    'statut' => false,
                    'date_envoi' => now(),
                ]);
            }
        } catch (Exception $e) {
            Log::error("Error sending mission completion notification to ecole: " . $e->getMessage());
        }
    }

    /**
     * Placeholder method to get admin users.
     *
     * This should be replaced with actual logic to identify admin users.
     *
     * @return \Illuminate\Database\Eloquent\Collection|array
     */
    private function getAdminUsers()
    {
        // Example: Fetch users with a specific role, or from a configuration.
        // This is a placeholder and needs to be implemented based on your application's admin identification logic.
        // For demonstration, returning an empty collection.
        return User::whereHas('roles', function ($query) {
            $query->where('name', 'admin'); // Assuming a 'roles' relationship and 'admin' role name
        })->get();
    }
}Services;

use App\Enums\CanalNotification;
use App\Enums\TypeNotification;
use App\Models\Notification;
use App\Models\User;
use App\Models\Technicien;
use App\Repositories\Contracts\TechnicienRepositoryInterface;
use Exception;
use Illuminate\Support\Facades\Log;

class NotificationService implements NotificationServiceInterface
{
    protected Notification $notificationModel;
    protected TechnicienRepositoryInterface $technicienRepository;

    public function __construct(Notification $notificationModel, TechnicienRepositoryInterface $technicienRepository)
    {
        $this->notificationModel = $notificationModel;
        $this->technicienRepository = $technicienRepository;
    }

    /**
     * Send a notification to admin users about a validated payment.
     *
     * @param array $paymentDetails
     * @return void
     */
    public function sendAdminPaymentNotification(array $paymentDetails): void
    {
        try {
            // TODO: Implement logic to retrieve actual admin users.
            // For now, let's assume we have a method to get them.
            $adminUsers = $this->getAdminUsers();

            foreach ($adminUsers as $admin) {
                $this->notificationModel->create([
                    'notifiable_id' => $admin->id,
                    'notifiable_type' => User::class,
                    'type' => TypeNotification::SYSTEME,
                    'canal' => CanalNotification::SYSTEME,
                    'message' => "Un paiement de {$paymentDetails['montant']} a été validé pour l'abonnement {$paymentDetails['abonnement_id']}.",
                    'titre' => 'Nouveau paiement validé',
                    'data' => $paymentDetails,
                    'statut' => false, // Not yet read
                    'date_envoi' => now(),
                ]);
            }
        } catch (Exception $e) {
            Log::error("Error sending admin payment notification: " . $e->getMessage());
        }
    }

    /**
     * Send a notification to technicians in a specific city about a new OrdreMission.
     *
     * @param string $villeId
     * @param array $ordreMissionDetails
     * @return void
     */
    public function sendNewOrdreMissionNotificationToTechnicians(string $villeId, array $ordreMissionDetails): void
    {
        try {
            $technicians = $this->technicienRepository->findAllBy(['ville_id' => $villeId]);

            foreach ($technicians as $technician) {
                $this->notificationModel->create([
                    'notifiable_id' => $technician->id,
                    'notifiable_type' => Technicien::class,
                    'type' => TypeNotification::SYSTEME,
                    'canal' => CanalNotification::SYSTEME,
                    'message' => "Un nouvel ordre de mission ({$ordreMissionDetails['numero_ordre']}) a été généré dans votre ville ({$ordreMissionDetails['ville_nom']}).",
                    'titre' => 'Nouvel Ordre de Mission Disponible',
                    'data' => $ordreMissionDetails,
                    'statut' => false,
                    'date_envoi' => now(),
                ]);
            }
        } catch (Exception $e) {
            Log::error("Error sending new ordre mission notification to technicians: " . $e->getMessage());
        }
    }

    public function sendCandidatureValidationNotification(string $technicienId, array $candidatureDetails): void
    {
        try {
            $technician = $this->technicienRepository->find($technicienId);

            if ($technician) {
                $this->notificationModel->create([
                    'notifiable_id' => $technician->id,
                    'notifiable_type' => Technicien::class,
                    'type' => TypeNotification::SYSTEME,
                    'canal' => CanalNotification::SYSTEME,
                    'message' => "Votre candidature pour l'ordre de mission {$candidatureDetails['numero_ordre']} a été validée.",
                    'titre' => 'Candidature Validée',
                    'data' => $candidatureDetails,
                    'statut' => false,
                    'date_envoi' => now(),
                ]);
            }
        } catch (Exception $e) {
            Log::error("Error sending candidature validation notification: " . $e->getMessage());
        }
    }


    public function sendAdminCandidatureSubmissionNotification(array $candidatureDetails): void
    {
        try {
            $adminUsers = $this->getAdminUsers();

            foreach ($adminUsers as $admin) {
                $this->notificationModel->create([
                    'notifiable_id' => $admin->id,
                    'notifiable_type' => User::class,
                    'type' => TypeNotification::SYSTEME,
                    'canal' => CanalNotification::SYSTEME,
                    'message' => "Une nouvelle candidature a été soumise par le technicien {$candidatureDetails['technicien_nom']} pour l'ordre de mission {$candidatureDetails['numero_ordre']}.",
                    'titre' => 'Nouvelle Candidature Soumise',
                    'data' => $candidatureDetails,
                    'statut' => false,
                    'date_envoi' => now(),
                ]);
            }
        } catch (Exception $e) {
            Log::error("Error sending admin candidature submission notification: " . $e->getMessage());
        }
    }

    /**
     * Placeholder method to get admin users.
     * This should be replaced with actual logic to identify admin users.
     *
     * @return \Illuminate\Database\Eloquent\Collection|array
     */
    private function getAdminUsers()
    {
        // Example: Fetch users with a specific role, or from a configuration.
        // This is a placeholder and needs to be implemented based on your application's admin identification logic.
        // For demonstration, returning an empty collection.
        return User::whereHas('roles', function ($query) {
            $query->where('name', 'admin'); // Assuming a 'roles' relationship and 'admin' role name
        })->get();
    }
}

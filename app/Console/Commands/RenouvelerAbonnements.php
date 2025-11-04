<?php

namespace App\Console\Commands;

use App\Enums\StatutAbonnement;
use App\Services\Contracts\AbonnementServiceInterface;
use App\Services\Contracts\PaiementServiceInterface;
use App\Services\SmsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RenouvelerAbonnements extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'abonnements:renouveler-auto';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Renouvelle automatiquement les abonnements et alerte les admins.';

    /**
     * Execute the console command.
     */
    public function handle(
        AbonnementServiceInterface $abonnementService,
        PaiementServiceInterface $paiementService,
        SmsService $smsService
    ) {
        $this->info('Début du processus de renouvellement automatique...');
        Log::info('CRON - Début du renouvellement automatique.');

        try {
            DB::beginTransaction();

            $abonnementsARenouveler = $abonnementService->getRepository()->model
                ->where('statut', StatutAbonnement::ACTIF)
                ->where('auto_renouvellement', true)
                ->where('date_fin', '<=', now()->addDays(7))
                ->with('ecole.user.userInfo') // Charger la relation pour le numéro de téléphone
                ->get();

            $count = 0;
            foreach ($abonnementsARenouveler as $abonnement) {
                $this->info("Traitement de l'abonnement #{$abonnement->id} pour l'école {$abonnement->ecole->nom}");

                // 1. Alerter par SMS
                $telephone = $abonnement->ecole->user->userInfo->telephone ?? null;
                if ($telephone) {
                    $message = "Bonjour, votre abonnement Sirène d'École arrive à expiration et sera renouvelé automatiquement.";
                    $smsService->sendSms($telephone, $message);
                    $this->info("SMS d'alerte envoyé au $telephone");
                } else {
                    $this->warn("Aucun numéro de téléphone trouvé pour l'école {$abonnement->ecole->nom}.");
                }

                // 2. Créer le nouvel abonnement en attente
                $response = $abonnementService->renouvelerAbonnement($abonnement->id);
                $responseData = $response->getData(true);
                $nouvelAbonnementId = $responseData['data']['id'] ?? null;

                if (!$nouvelAbonnementId) {
                    $this->error("Échec de la création du nouvel abonnement pour l'abonnement parent #{$abonnement->id}");
                    continue;
                }
                $this->info("Nouvel abonnement #{$nouvelAbonnementId} créé.");

                // 3. Lancer l'auto-paiement
                $this->info("Lancement de l'auto-paiement pour le nouvel abonnement #{$nouvelAbonnementId}...");
                $paiementResponse = $paiementService->processAutomaticPayment($nouvelAbonnementId);
                $paiementResponseData = $paiementResponse->getData(true);

                if ($paiementResponseData['success']) {
                    $this->info("Auto-paiement réussi pour l'abonnement #{$nouvelAbonnementId}.");
                }
                else {
                    $this->error("Échec de l'auto-paiement pour l'abonnement #{$nouvelAbonnementId}: " . ($paiementResponseData['message'] ?? 'Erreur inconnue'));
                }

                $count++;
            }

            DB::commit();
            $this->info("$count abonnements ont été traités pour le renouvellement automatique.");
            Log::info("CRON - Succès : $count abonnements traités.");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Une erreur est survenue lors du renouvellement automatique.');
            $this->error($e->getMessage());
            Log::error('CRON - Erreur lors du renouvellement automatique : ' . $e->getMessage());
            return Command::FAILURE;
        }

        $this->info('Processus de renouvellement automatique terminé.');
        return Command::SUCCESS;
    }
}

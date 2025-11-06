<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class CandidatureValidationNotification extends Notification
{
    use Queueable;

    protected array $candidatureDetails;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(array $candidatureDetails)
    {
        $this->candidatureDetails = $candidatureDetails;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database']; // Store in database
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line("Votre candidature pour l'ordre de mission {$this->candidatureDetails['numero_ordre']} a été validée.")
                    ->action('Voir la mission', url('/')) // TODO: Link to mission details
                    ->line('Merci d'utiliser notre application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'title' => 'Candidature Validée',
            'message' => "Votre candidature pour l'ordre de mission {$this->candidatureDetails['numero_ordre']} a été validée.",
            'ordre_mission_id' => $this->candidatureDetails['ordre_mission_id'],
            'numero_ordre' => $this->candidatureDetails['numero_ordre'],
            'type' => 'candidature_validation',
        ];
    }
}

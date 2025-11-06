<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class NewMissionOrderNotification extends Notification
{
    use Queueable;

    protected array $ordreMissionDetails;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(array $ordreMissionDetails)
    {
        $this->ordreMissionDetails = $ordreMissionDetails;
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
                    ->line("Un nouvel ordre de mission ({$this->ordreMissionDetails['numero_ordre']}) a été généré dans votre ville ({$this->ordreMissionDetails['ville_nom']}).")
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
            'title' => 'Nouvel Ordre de Mission Disponible',
            'message' => "Un nouvel ordre de mission ({$this->ordreMissionDetails['numero_ordre']}) a été généré dans votre ville ({$this->ordreMissionDetails['ville_nom']}).",
            'ordre_mission_id' => $this->ordreMissionDetails['id'],
            'numero_ordre' => $this->ordreMissionDetails['numero_ordre'],
            'ville_nom' => $this->ordreMissionDetails['ville_nom'],
            'type' => 'new_mission_order',
        ];
    }
}

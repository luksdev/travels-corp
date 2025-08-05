<?php

declare(strict_types = 1);

namespace App\Notifications;

use App\Models\TravelRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Travel Request Status Changed Notification
 */
class TravelRequestStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public TravelRequest $travelRequest,
        public string $oldStatus,
        public string $newStatus
    ) {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $statusText = $this->newStatus === 'approved' ? 'aprovada' : 'cancelada';
        $subject    = "Solicitação de Viagem " . $statusText;

        return (new MailMessage())
            ->subject($subject)
            ->greeting("Olá " . $notifiable->name . "!")
            ->line("Sua solicitação de viagem para " . $this->travelRequest->destination . " foi " . $statusText . ".")
            ->line("Data de Partida: " . $this->travelRequest->departure_date->format('j/m/Y'))
            ->when($this->travelRequest->return_date, function ($mail) {
                return $mail->line("Data de Retorno: " . $this->travelRequest->return_date->format('j/m/Y'));
            })
            ->line('Obrigado por usar a TravelsCorp!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $statusText = $this->newStatus === 'approved' ? 'aprovada' : 'cancelada';

        return [
            'travel_request_id' => $this->travelRequest->id,
            'destination'       => $this->travelRequest->destination,
            'old_status'        => $this->oldStatus,
            'new_status'        => $this->newStatus,
            'departure_date'    => $this->travelRequest->departure_date,
            'return_date'       => $this->travelRequest->return_date,
            'message'           => "Sua solicitação de viagem para " . $this->travelRequest->destination . " foi " . $statusText . ".",
        ];
    }
}

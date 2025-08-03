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
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $statusText = $this->newStatus === 'approved' ? 'approved' : 'rejected';
        $subject    = "Travel Request {$statusText}";

        return (new MailMessage())
            ->subject($subject)
            ->greeting("Hello {$notifiable->name}!")
            ->line("Your travel request to {$this->travelRequest->destination} has been {$statusText}.")
            ->line("Departure Date: {$this->travelRequest->departure_date->format('M j, Y')}")
            ->when($this->travelRequest->return_date, function ($mail) {
                return $mail->line("Return Date: {$this->travelRequest->return_date->format('M j, Y')}");
            })
            ->line('Thank you for using OnHappy!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'travel_request_id' => $this->travelRequest->id,
            'destination'       => $this->travelRequest->destination,
            'old_status'        => $this->oldStatus,
            'new_status'        => $this->newStatus,
            'departure_date'    => $this->travelRequest->departure_date,
            'return_date'       => $this->travelRequest->return_date,
            'message'           => "Your travel request to {$this->travelRequest->destination} has been {$this->newStatus}.",
        ];
    }
}

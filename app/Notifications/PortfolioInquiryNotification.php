<?php

namespace App\Notifications;

use App\Enums\QueueName;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PortfolioInquiryNotification extends Notification
{
    use Queueable;

    private string $name;
    private string $email;
    private string $message;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $name, string $email, string $message)
    {
        $this->queue = QueueName::NOTIFICATIONS->value;
        $this->name = $name;
        $this->email = $email;
        $this->message = $message;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'email' => $this->email,
            'name' => $this->name,
            'message' => $this->message,
            'subject' => $this->name . ' has sent you a message.',
        ];
    }
}

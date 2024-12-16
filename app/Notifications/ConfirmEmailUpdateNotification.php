<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\URL;

class ConfirmEmailUpdateNotification extends Notification
{
    private string $userGivenName;
    private int $expirationTimeMinutes;
    private string $newEmail;
    private int $notifiableId;
    public string $actionUrl;

    public function __construct(object $notifiable, string $newEmail, ?int $expirationTimeMinutes = null)
    {
        $this->userGivenName = $notifiable->userProfile->given_name;
        $this->notifiableId = $notifiable->getKey();

        if (is_null($expirationTimeMinutes)) {
            $expirationTimeMinutes = Config::get('df_profile.email_update.expire', 60);
        }
        $this->expirationTimeMinutes = $expirationTimeMinutes;

        $this->newEmail = $newEmail;
        $this->actionUrl = $this->verificationUrl($this->notifiableId);
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $appName = config('app.name');

        return (new MailMessage())
            ->subject('Confirm Your Email Address Update')
            ->greeting('Hey, ' . $this->userGivenName . '!')
            ->line("You requested an email update at $appName.")
            ->line(
                "To confirm your email change, click the button below.
               Please note that this link will expire in $this->expirationTimeMinutes minutes."
            )
            ->action('Confirm Email Address', $this->actionUrl)
            ->line('If you did not request an email change, please ignore this email.');
    }

    private function verificationUrl(int $notifiableId): string
    {
        return URL::temporarySignedRoute(
            'profile.confirmEmailUpdate',
            Carbon::now()->addMinutes($this->expirationTimeMinutes),
            [
                'user' => $notifiableId,
                'email' => urlencode(Crypt::encrypt($this->newEmail)),
            ]
        );
    }
}

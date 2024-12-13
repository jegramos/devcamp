<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class VerifyAccountNotification extends VerifyEmail
{
    private string $userGivenName;
    private string $temporaryPassword;
    private int $expirationTimeMinutes;
    public string $actionUrl;

    public function __construct(mixed $notifiable, string $temporaryPassword, ?int $expirationTimeMinutes = null)
    {
        /** @var User $notifiable */
        $this->userGivenName = $notifiable->userProfile->given_name;

        if (is_null($expirationTimeMinutes)) {
            $expirationTimeMinutes = Config::get('auth.verification.expire', 60);
        }

        $this->temporaryPassword = $temporaryPassword;
        $this->expirationTimeMinutes = $expirationTimeMinutes;
        $this->actionUrl = $this->verificationUrl($notifiable);
    }

    protected function buildMailMessage($url): MailMessage
    {
        $appName = config('app.name');

        return (new MailMessage())
            ->subject('Verify Your Account')
            ->greeting('Hey, ' . $this->userGivenName . '!')
            ->line("A $appName account has been created for you.")
            ->line('Your temporary password: ' . $this->temporaryPassword)
            ->line('Please change this immediately after logging in.')
            ->line(
                "Please click the button below to complete the verification process of your account. 
               Note that this link will expire in $this->expirationTimeMinutes minutes."
            )
            ->action('Verify Account', $url)
            ->line('If you did not ask for an account to be created, please ignore this email.');
    }

    protected function verificationUrl($notifiable)
    {
        if (static::$createUrlCallback) {
            return call_user_func(static::$createUrlCallback, $notifiable);
        }

        return URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes($this->expirationTimeMinutes),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }
}

<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class VerifyEmailNotification extends VerifyEmail
{
    private string $userGivenName;
    private int $expirationTimeMinutes;
    public string $actionUrl;

    public function __construct(mixed $notifiable, ?int $expirationTimeMinutes = null)
    {
        /** @var User $notifiable */
        $this->userGivenName = $notifiable->userProfile->given_name;

        if (is_null($expirationTimeMinutes)) {
            $expirationTimeMinutes = Config::get('auth.verification.expire', 60);
        }

        $this->expirationTimeMinutes = $expirationTimeMinutes;

        $this->actionUrl = $this->verificationUrl($notifiable);
    }

    protected function buildMailMessage($url): MailMessage
    {
        $appName = config('app.name');

        return (new MailMessage())
            ->subject('Verify Your Email Address')
            ->greeting('Hey, ' . $this->userGivenName . '!')
            ->line("Thank you for registering to $appName.")
            ->line(
                "Please click the button below to verify your email address. 
               Note that this link will expire in $this->expirationTimeMinutes minutes."
            )
            ->action('Verify Email Address', $url)
            ->line('If you did not create an account, please ignore this email.');
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

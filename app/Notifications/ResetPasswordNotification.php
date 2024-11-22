<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Lang;

class ResetPasswordNotification extends ResetPassword
{
    public function __construct(string $token)
    {
        parent::__construct($token);
    }

    /**
     * @inheritDoc
     */
    public function via(mixed $notifiable): array
    {
        return ['mail'];
    }

    /**
     * @inheritDoc
     */
    protected function buildMailMessage($url): MailMessage
    {
        return (new MailMessage())
            ->subject(Lang::get('Reset Password Notification'))
            ->line(Lang::get(
                'You are receiving this email because we received a password reset request for your account.'
            ))
            ->action(Lang::get('Reset Password'), $url)
            ->line(Lang::get(
                'This password reset link will expire in :count minutes.',
                ['count' => config('auth.passwords.' . config('auth.defaults.passwords') . '.expire')]
            ))
            ->line(Lang::get('If you did not request a password reset, no further action is required.'));
    }

    protected function resetUrl($notifiable)
    {
        if (static::$createUrlCallback) {
            return call_user_func(static::$createUrlCallback, $notifiable, $this->token);
        }

        return url(route('auth.password.showResetPasswordForm', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));
    }
}

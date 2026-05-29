<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordResetPinNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $code,
        private readonly int $expiresInMinutes,
    ) {
    }

    /**
     * @return array<int, string>
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your password reset code')
            ->line('Use this 6-digit code to reset your password:')
            ->line($this->code)
            ->line("This code will expire in {$this->expiresInMinutes} minutes.")
            ->line('If you did not request a password reset, you can ignore this message.');
    }
}


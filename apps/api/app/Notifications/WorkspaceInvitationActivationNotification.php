<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WorkspaceInvitationActivationNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $activationUrl,
    ) {}

    public function activationUrl(): string
    {
        return $this->activationUrl;
    }

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Activate your SupportFlow account')
            ->greeting('You were invited to SupportFlow')
            ->line('Set your password using the activation link below to sign in with your credentials.')
            ->action('Set password', $this->activationUrl)
            ->line('This activation link expires in 7 days and can be used once.');
    }
}

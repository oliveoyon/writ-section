<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class LawyerActivationNotification extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $hours = max(1, (int) config('auth.lawyer_activation_expire', 24));
        $activationUrl = URL::temporarySignedRoute(
            'lawyer.activate',
            now()->addHours($hours),
            [
                'user' => $notifiable->getKey(),
                'hash' => sha1((string) $notifiable->email),
            ]
        );

        return (new MailMessage)
            ->subject('Activate your RTFTS lawyer account')
            ->greeting('Dear '.$notifiable->name.',')
            ->line('Your lawyer account has been registered in the Real Time File Tracking System (RTFTS).')
            ->line('Please confirm your email address to activate the account.')
            ->action('Activate Account', $activationUrl)
            ->line("This activation link will expire in {$hours} hours.")
            ->line('If you did not register this account, no action is required.');
    }
}

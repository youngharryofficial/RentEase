<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeNotification extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Welcome to RentEase')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your RentEase account has been created successfully.')
            ->line('You can now view your lease, pay rent with M-Pesa and send maintenance requests.')
            ->action('Go to my dashboard', url('/dashboard'))
            ->line('Thank you for using RentEase.');
    }
}

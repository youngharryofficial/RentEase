<?php

namespace App\Notifications;

use App\Models\MaintenanceRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MaintenanceStatusUpdated extends Notification
{
    use Queueable;

    public function __construct(public MaintenanceRequest $maintenance)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Maintenance update: ' . $this->maintenance->title)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('The status of your maintenance request "' . $this->maintenance->title . '" is now: '
                . ucfirst(str_replace('_', ' ', $this->maintenance->status)) . '.')
            ->action('View my requests', url('/maintenance'));
    }
}

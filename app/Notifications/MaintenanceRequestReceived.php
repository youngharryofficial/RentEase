<?php

namespace App\Notifications;

use App\Models\MaintenanceRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MaintenanceRequestReceived extends Notification
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
        $m = $this->maintenance->loadMissing('tenant', 'unit.property');

        return (new MailMessage)
            ->subject('New maintenance request: ' . $m->title)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line($m->tenant->name . ' sent a maintenance request.')
            ->line('Unit: ' . $m->unit->property->name . ', ' . $m->unit->unit_number)
            ->line('Priority: ' . ucfirst($m->priority))
            ->line('Details: ' . $m->description)
            ->action('View requests', url('/maintenance'));
    }
}

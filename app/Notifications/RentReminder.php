<?php

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RentReminder extends Notification
{
    use Queueable;

    public function __construct(public Invoice $invoice)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $overdue = $this->invoice->due_date->isPast();
        $unit = $this->invoice->lease->unit;

        return (new MailMessage)
            ->subject(($overdue ? 'Rent overdue: ' : 'Rent due soon: ') . $this->invoice->period)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line($overdue
                ? 'Your rent for ' . $this->invoice->period . ' is overdue.'
                : 'Your rent for ' . $this->invoice->period . ' is due soon.')
            ->line('Unit: ' . $unit->property->name . ', ' . $unit->unit_number)
            ->line('Balance: KES ' . number_format($this->invoice->balance))
            ->line('Due date: ' . $this->invoice->due_date->format('d M Y'))
            ->action('Pay with M-Pesa', url('/invoices/' . $this->invoice->id . '/pay'))
            ->line('Please ignore this message if you have already paid.');
    }
}

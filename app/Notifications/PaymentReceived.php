<?php

namespace App\Notifications;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentReceived extends Notification
{
    use Queueable;

    public function __construct(public Payment $payment)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $invoice = $this->payment->invoice;
        $balance = (float) $invoice->balance;

        return (new MailMessage)
            ->subject('Payment received - KES ' . number_format($this->payment->amount) . ' (' . $this->payment->mpesa_receipt . ')')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('We have received your rent payment. Thank you!')
            ->line('Amount: KES ' . number_format($this->payment->amount))
            ->line('Period: ' . $invoice->period)
            ->line('M-Pesa receipt: ' . $this->payment->mpesa_receipt)
            ->line('Date: ' . $this->payment->paid_at?->format('d M Y, H:i'))
            ->line($balance > 0
                ? 'Remaining balance for this period: KES ' . number_format($balance)
                : 'This invoice is now fully paid.')
            ->action('View my dashboard', url('/dashboard'));
    }
}

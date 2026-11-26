<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Notifications\RentReminder;
use Illuminate\Console\Command;

class SendRentReminders extends Command
{
    protected $signature = 'rent:send-reminders';

    protected $description = 'Email tenants whose rent is due within 3 days or already overdue';

    public function handle(): int
    {
        // Flag unpaid invoices that are past their due date
        Invoice::where('status', 'unpaid')
            ->whereDate('due_date', '<', today())
            ->update(['status' => 'overdue']);

        $invoices = Invoice::with('lease.tenant', 'lease.unit.property')
            ->where('balance', '>', 0)
            ->whereIn('status', ['unpaid', 'partial', 'overdue'])
            ->whereDate('due_date', '<=', today()->addDays(3))
            ->get();

        $sent = 0;

        foreach ($invoices as $invoice) {
            try {
                $invoice->lease->tenant->notify(new RentReminder($invoice));
                $sent++;
            } catch (\Throwable $e) {
                $this->error('Could not email ' . $invoice->lease->tenant->email . ': ' . $e->getMessage());
            }
        }

        $this->info("Sent {$sent} reminder(s).");

        return self::SUCCESS;
    }
}

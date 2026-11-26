<?php

namespace App\Listeners;

use App\Notifications\WelcomeNotification;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Log;

class SendWelcomeNotification
{
    public function handle(Registered $event): void
    {
        try {
            $event->user->notify(new WelcomeNotification());
        } catch (\Throwable $e) {
            // A mail problem must never stop someone from registering
            Log::error('Welcome email failed: ' . $e->getMessage());
        }
    }
}

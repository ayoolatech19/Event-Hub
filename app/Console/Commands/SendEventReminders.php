<?php

namespace App\Console\Commands;

use App\Mail\EventReminder;
use App\Models\Booking;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendEventReminders extends Command
{
    protected $signature = 'bookings:remind';

    protected $description = 'Email attendees whose event starts within 24 hours';

    public function handle(): int
    {
        $sent = 0;

        Booking::with(['user', 'event'])
            ->where('status', 'confirmed')
            ->whereNull('reminder_sent_at')
            ->whereHas('event', function ($query) {
                $query->whereBetween('date', [now(), now()->addDay()]);
            })
            ->chunkById(100, function ($bookings) use (&$sent) {
                foreach ($bookings as $booking) {
                    Mail::to($booking->user->email)->send(new EventReminder($booking));
                    $booking->update(['reminder_sent_at' => now()]);
                    $sent++;
                }
            });

        $this->info("Sent {$sent} reminder(s).");

        return self::SUCCESS;
    }
}
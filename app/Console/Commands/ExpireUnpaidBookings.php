<?php

namespace App\Console\Commands;

use App\Models\Booking;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExpireUnpaidBookings extends Command
{
    protected $signature = 'bookings:expire';

    protected $description = 'Expire unpaid bookings past their hold time and release the seats';

    public function handle(): int
    {
        $expired = 0;

        Booking::where('status', 'pending')
            ->where('expires_at', '<', now())
            ->chunkById(100, function ($bookings) use (&$expired) {
                foreach ($bookings as $booking) {
                    DB::transaction(function () use ($booking, &$expired) {
                        $locked = Booking::whereKey($booking->id)->lockForUpdate()->first();

                        if ($locked->status !== 'pending') {
                            return;
                        }

                        $locked->update(['status' => 'expired']);
                        $locked->event()->lockForUpdate()->first();
                        $locked->event->decrement('tickets_sold', $locked->quantity);

                        $expired++;
                    });
                }
            });

        if ($expired > 0) {
            Log::info('Expired unpaid bookings', ['count' => $expired]);
        }

        $this->info("Expired {$expired} booking(s).");

        return self::SUCCESS;
    }
}
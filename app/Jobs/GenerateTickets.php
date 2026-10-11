<?php

namespace App\Jobs;

use App\Models\Booking;
use App\Models\Ticket;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GenerateTickets implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public array $backoff = [10, 30, 60];

    public function __construct(public int $bookingId) {}

    public function handle(): void
    {
        $booking = Booking::find($this->bookingId);

        if (! $booking || $booking->status !== 'confirmed') {
            return;
        }

        if ($booking->tickets()->exists()) {
            return;
        }

        for ($i = 0; $i < $booking->quantity; $i++) {
            $code = Str::upper(Str::random(32));

            $result = (new Builder())->build(
                data: $code,
                writer: new SvgWriter(),
                size: 400,
                margin: 10,
            );

            $path = "tickets/{$code}.svg";
            Storage::disk('public')->put($path, $result->getString());

            Ticket::create([
                'booking_id' => $booking->id,
                'code' => $code,
                'qr_path' => $path,
                'status' => 'valid',
            ]);
        }
    }
}
<?php

namespace App\Jobs;

use App\Mail\BookingConfirmed;
use App\Models\Booking;
use App\Models\Payment;
use App\Services\PaystackService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
class ProcessSuccessfulPayment implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public array $backoff = [10, 30, 60];

    public function __construct(
        public string $reference,
        public array $data,
    ) {}

    public function handle(PaystackService $paystack): void
    {
        $booking = Booking::where('reference', $this->reference)->first();

        if (! $booking) {
            Log::warning('Webhook for unknown booking', ['reference' => $this->reference]);
            return;
        }

        if ($booking->status === 'confirmed') {
            return; // already done
        }

        // never trust the webhook body — ask Paystack directly
 // never trust the webhook body — ask Paystack directly
try {
    $verified = $paystack->verify($this->reference);
} catch (RuntimeException $e) {
    Log::warning('Could not verify payment with Paystack', [
        'reference' => $this->reference,
    ]);
    return;
}   

        if (($verified['status'] ?? null) !== 'success') {
            Log::warning('Webhook claimed success but verify disagreed', [
                'reference' => $this->reference,
            ]);
            return;
        }

        if ((int) $verified['amount'] !== (int) $booking->total_price) {
            Log::critical('Payment amount mismatch', [
                'reference' => $this->reference,
                'expected' => $booking->total_price,
                'received' => $verified['amount'],
            ]);
            return;
        }

        DB::transaction(function () use ($booking, $verified) {
            $booking->update([
                'status' => 'confirmed',
                'paid_at' => now(),
            ]);

            Payment::where('reference', $booking->reference)->update([
                'status' => 'success',
                'payload' => $verified,
                'paid_at' => now(),
            ]);
        });

        Mail::to($booking->user->email)->send(new BookingConfirmed($booking->load(['event', 'user'])));
    }
}
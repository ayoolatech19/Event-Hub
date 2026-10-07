<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use App\Services\PaystackService;
use Illuminate\Http\Request;
use RuntimeException;
use App\Models\ProcessedWebhookEvent;
use App\Jobs\ProcessSuccessfulPayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function pay(Request $request, string $reference, PaystackService $paystack)
    {

        $booking = Booking::where('reference', $reference)->firstOrFail();

        if ($booking->user_id !== $request->user()->id) {
            abort(403, 'This booking is not yours.');
        }

        if ($booking->status === 'confirmed') {
            return response()->json(['message' => 'This booking is already paid.'], 422);
        }

        if ($booking->status !== 'pending') {
            return response()->json(['message' => 'This booking can no longer be paid for.'], 422);
        }

        try {
            $data = $paystack->initialize(
                reference: $booking->reference,
                amountInKobo: $booking->total_price,
                email: $request->user()->email,
                metadata: ['booking_id' => $booking->id],
            );
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 502);
        }

        Payment::updateOrCreate(
            ['reference' => $booking->reference],
            [
                'booking_id' => $booking->id,
                'amount' => $booking->total_price,
                'status' => 'pending',
            ]
        );

        return response()->json([
            'data' => [
                'checkout_url' => $data['authorization_url'],
                'reference' => $booking->reference,
                'amount' => $booking->total_price,
                'amount_formatted' => 'NGN ' . number_format($booking->total_price / 100, 2),
            ],
        ]);
    }

public function webhook(Request $request)
{
    $raw = $request->getContent();
    $signature = (string) $request->header('x-paystack-signature');

    $expected = hash_hmac('sha512', $raw, config('services.paystack.secret'));

    if (! hash_equals($expected, $signature)) {
        Log::warning('Paystack webhook signature mismatch', [
            'ip' => $request->ip(),
            'body_preview' => substr($raw, 0, 200),
        ]);

        return response()->json(['message' => 'Invalid signature.'], 401);
    }

    $payload = json_decode($raw, true);
    $eventType = $payload['event'] ?? null;
    $data = $payload['data'] ?? [];
    $reference = $data['reference'] ?? null;

    // a stable id for this delivery
    $eventId = $data['id'] ?? null;
    $eventId = $eventId ? "{$eventType}:{$eventId}" : "{$eventType}:{$reference}";

    try {
        ProcessedWebhookEvent::create([
            'event_id' => $eventId,
            'event_type' => (string) $eventType,
            'reference' => $reference,
        ]);
    } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
        // already handled — say OK so Paystack stops retrying
        return response()->json(['message' => 'Already processed.'], 200);
    }

    if ($eventType === 'charge.success' && $reference) {
        ProcessSuccessfulPayment::dispatch($reference, $data);
    }

    if ($eventType === 'charge.failed' && $reference) {
        Payment::where('reference', $reference)->update([
            'status' => 'failed',
            'payload' => $data,
        ]);
    }

    return response()->json(['message' => 'ok'], 200);
}
}
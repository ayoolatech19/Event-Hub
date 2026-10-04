<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use App\Services\PaystackService;
use Illuminate\Http\Request;
use RuntimeException;

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
}
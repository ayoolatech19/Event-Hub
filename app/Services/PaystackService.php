<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class PaystackService
{
    public function initialize(string $reference, int $amountInKobo, string $email, array $metadata = []): array
    {
        $response = Http::withToken(config('services.paystack.secret'))
            ->acceptJson()
            ->timeout(15)
            ->post(config('services.paystack.url') . '/transaction/initialize', [
                'reference' => $reference,
                'amount' => $amountInKobo,
                'email' => $email,
                'metadata' => $metadata,
            ]);

        if ($response->failed()) {
            Log::error('Paystack initialize failed', [
                'reference' => $reference,
                'status' => $response->status(),
                'body' => $response->json(),
            ]);

            throw new RuntimeException('Could not start payment. Please try again.');
        }

        return $response->json('data');
    }

    public function verify(string $reference): array
    {
        $response = Http::withToken(config('services.paystack.secret'))
            ->acceptJson()
            ->timeout(15)
            ->get(config('services.paystack.url') . "/transaction/verify/{$reference}");

        if ($response->failed()) {
            Log::error('Paystack verify failed', [
                'reference' => $reference,
                'status' => $response->status(),
            ]);

            throw new RuntimeException('Could not verify payment.');
        }

        return $response->json('data');
    }
}
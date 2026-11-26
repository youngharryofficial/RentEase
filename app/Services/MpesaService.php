<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class MpesaService
{
    private function baseUrl(): string
    {
        return config('mpesa.env') === 'production'
            ? 'https://api.safaricom.co.ke'
            : 'https://sandbox.safaricom.co.ke';
    }

    /**
     * Get an OAuth access token (cached for 50 minutes; Safaricom tokens last 1 hour).
     */
    public function token(): string
    {
        $key = config('mpesa.consumer_key');
        $secret = config('mpesa.consumer_secret');

        if (! $key || ! $secret) {
            throw new RuntimeException('M-Pesa consumer key/secret are missing in .env');
        }

        return Cache::remember('mpesa_access_token', 3000, function () use ($key, $secret) {
            $response = Http::withBasicAuth($key, $secret)
                ->timeout(30)
                ->get($this->baseUrl() . '/oauth/v1/generate', ['grant_type' => 'client_credentials']);

            if (! $response->successful() || ! $response->json('access_token')) {
                throw new RuntimeException('Could not get M-Pesa access token: ' . $response->body());
            }

            return $response->json('access_token');
        });
    }

    /**
     * Send an STK Push (Lipa na M-Pesa Online) request.
     * $phone must be in 2547XXXXXXXX format.
     */
    public function stkPush(string $phone, int $amount, string $reference, string $description): array
    {
        $shortcode = config('mpesa.shortcode');
        $passkey = config('mpesa.passkey');
        $callback = config('mpesa.callback_url');

        if (! $passkey || ! $callback) {
            throw new RuntimeException('M-Pesa passkey or callback URL is missing in .env');
        }

        $timestamp = now()->format('YmdHis');
        $password = base64_encode($shortcode . $passkey . $timestamp);

        $response = Http::withToken($this->token())
            ->acceptJson()
            ->timeout(30)
            ->post($this->baseUrl() . '/mpesa/stkpush/v1/processrequest', [
                'BusinessShortCode' => $shortcode,
                'Password' => $password,
                'Timestamp' => $timestamp,
                'TransactionType' => 'CustomerPayBillOnline',
                'Amount' => $amount,
                'PartyA' => $phone,
                'PartyB' => $shortcode,
                'PhoneNumber' => $phone,
                'CallBackURL' => $callback,
                'AccountReference' => $reference,
                'TransactionDesc' => $description,
            ]);

        return $response->json() ?? [];
    }
}

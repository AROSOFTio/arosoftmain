<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class PesapalService
{
    public function isConfigured(): bool
    {
        return filled($this->consumerKey()) && filled($this->consumerSecret());
    }

    public function submitOrder(array $payload): array
    {
        return $this->request('post', '/api/Transactions/SubmitOrderRequest', $payload, true);
    }

    public function transactionStatus(string $trackingId): array
    {
        return $this->request('get', '/api/Transactions/GetTransactionStatus', [
            'orderTrackingId' => $trackingId,
        ], true);
    }

    public function resolveIpnId(): string
    {
        $configuredIpnId = (string) config('services.pesapal.ipn_id', '');
        if ($configuredIpnId !== '') {
            return $configuredIpnId;
        }

        $cachedIpnId = Cache::get('pesapal:ipn_id');
        if (is_string($cachedIpnId) && $cachedIpnId !== '') {
            return $cachedIpnId;
        }

        $response = $this->request('post', '/api/URLSetup/RegisterIPN', [
            'url' => $this->ipnUrl(),
            'ipn_notification_type' => 'GET',
        ], true);

        $ipnId = (string) ($response['ipn_id'] ?? '');
        if ($ipnId === '') {
            throw new RuntimeException('Pesapal did not return an IPN ID.');
        }

        Cache::put('pesapal:ipn_id', $ipnId, now()->addDays(7));

        return $ipnId;
    }

    public function callbackUrl(): string
    {
        return rtrim((string) config('services.pesapal.callback_url'), '/');
    }

    public function ipnUrl(): string
    {
        return rtrim((string) config('services.pesapal.ipn_url'), '/');
    }

    public function currency(): string
    {
        return (string) config('services.pesapal.currency', 'UGX');
    }

    public function countryCode(): string
    {
        return (string) config('services.pesapal.country_code', 'UG');
    }

    private function request(string $method, string $path, array $payload = [], bool $authorized = false): array
    {
        $client = Http::acceptJson()->timeout(20);

        if ($authorized) {
            $client = $client->withToken($this->requestToken());
        }

        $url = $this->baseUrl().$path;
        $response = $method === 'get'
            ? $client->get($url, $payload)
            : $client->post($url, $payload);

        if (! $response->successful()) {
            $body = $response->json();
            $message = is_array($body)
                ? $this->extractErrorMessage($body)
                : 'Unexpected Pesapal response.';

            throw new RuntimeException("Pesapal request failed ({$response->status()}): {$message}");
        }

        $json = $response->json();
        if (! is_array($json)) {
            throw new RuntimeException('Pesapal returned a non-JSON response.');
        }

        return $json;
    }

    private function requestToken(): string
    {
        $cachedToken = Cache::get('pesapal:token');
        if (is_string($cachedToken) && $cachedToken !== '') {
            return $cachedToken;
        }

        $response = Http::acceptJson()
            ->timeout(20)
            ->post($this->baseUrl().'/api/Auth/RequestToken', [
                'consumer_key' => $this->consumerKey(),
                'consumer_secret' => $this->consumerSecret(),
            ]);

        if (! $response->successful()) {
            $body = $response->json();
            $message = is_array($body)
                ? $this->extractErrorMessage($body)
                : 'Unable to authenticate with Pesapal.';

            throw new RuntimeException("Pesapal token request failed ({$response->status()}): {$message}");
        }

        $payload = $response->json();
        $token = is_array($payload) ? (string) ($payload['token'] ?? '') : '';

        if ($token === '') {
            throw new RuntimeException('Pesapal did not return an access token.');
        }

        Cache::put('pesapal:token', $token, now()->addMinutes(4));

        return $token;
    }

    private function extractErrorMessage(array $payload): string
    {
        return (string) ($payload['message']
            ?? $payload['error']
            ?? $payload['status']
            ?? $payload['errorMessage']
            ?? 'Request failed.');
    }

    private function baseUrl(): string
    {
        return rtrim((string) config('services.pesapal.base_url', 'https://pay.pesapal.com/v3'), '/');
    }

    private function consumerKey(): string
    {
        return (string) config('services.pesapal.consumer_key', '');
    }

    private function consumerSecret(): string
    {
        return (string) config('services.pesapal.consumer_secret', '');
    }
}


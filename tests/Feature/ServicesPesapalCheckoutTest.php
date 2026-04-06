<?php

namespace Tests\Feature;

use App\Services\PesapalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Mockery\MockInterface;
use Tests\TestCase;

class ServicesPesapalCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_quote_payment_uses_callback_and_ipn_routes_when_env_urls_are_missing(): void
    {
        config([
            'services.pesapal.callback_url' => '',
            'services.pesapal.ipn_url' => '',
            'services.pesapal.ipn_id' => '',
        ]);

        $quoteId = 'QTE-20260406-1001';
        Cache::put("quote:{$quoteId}", [
            'quote_id' => $quoteId,
            'currency' => 'UGX',
            'customer' => [
                'full_name' => 'Client One',
                'email' => 'client@example.com',
                'phone' => '+256700000111',
                'company_name' => 'Client Co',
            ],
            'service' => [
                'key' => 'web_development',
                'label' => 'Web Development',
            ],
            'totals' => [
                'total' => 1000000.0,
                'deposit' => 400000.0,
            ],
            'payment' => [
                'status' => 'NOT_STARTED',
                'status_description' => 'Not started',
                'mode' => null,
                'amount_due' => null,
                'order_tracking_id' => null,
                'redirect_url' => null,
                'last_attempt_at' => null,
                'gateway_response' => null,
                'gateway_status_payload' => null,
            ],
        ], now()->addDay());

        $this->mock(PesapalService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('isConfigured')->once()->andReturn(true);
            $mock->shouldReceive('currency')->once()->andReturn('UGX');
            $mock->shouldReceive('countryCode')->once()->andReturn('UG');
            $mock->shouldReceive('resolveIpnId')
                ->once()
                ->withArgs(function (string $ipnUrl): bool {
                    return str_contains($ipnUrl, '/payments/pesapal/ipn');
                })
                ->andReturn('IPN-SVC-001');
            $mock->shouldReceive('submitOrder')
                ->once()
                ->withArgs(function (array $payload): bool {
                    return ($payload['notification_id'] ?? '') === 'IPN-SVC-001'
                        && str_contains((string) ($payload['callback_url'] ?? ''), '/payments/pesapal/callback')
                        && (float) ($payload['amount'] ?? 0.0) === 400000.0;
                })
                ->andReturn([
                    'redirect_url' => 'https://pay.pesapal.com/checkout/services-test',
                    'order_tracking_id' => 'TRK-SVC-001',
                ]);
        });

        $response = $this->post(route('services.quote.pay'), [
            'quote_id' => $quoteId,
            'payment_mode' => 'deposit',
        ]);

        $response->assertRedirect('https://pay.pesapal.com/checkout/services-test');

        $updatedQuote = Cache::get("quote:{$quoteId}");
        $this->assertIsArray($updatedQuote);
        $this->assertSame('PENDING', $updatedQuote['payment']['status']);
        $this->assertSame('TRK-SVC-001', $updatedQuote['payment']['order_tracking_id']);
        $this->assertSame(400000.0, (float) $updatedQuote['payment']['amount_due']);
    }
}


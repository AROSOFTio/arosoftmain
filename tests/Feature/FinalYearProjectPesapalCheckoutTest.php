<?php

namespace Tests\Feature;

use App\Models\FinalYearProjectOrder;
use App\Services\PesapalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery\MockInterface;
use Tests\TestCase;

class FinalYearProjectPesapalCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_redirects_to_pesapal_when_credentials_are_configured(): void
    {
        Storage::fake('local');

        $this->mock(PesapalService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('isConfigured')->once()->andReturn(true);
            $mock->shouldReceive('currency')->once()->andReturn('UGX');
            $mock->shouldReceive('countryCode')->once()->andReturn('UG');
            $mock->shouldReceive('resolveIpnId')
                ->once()
                ->withArgs(function (string $ipnUrl): bool {
                    return str_contains($ipnUrl, '/payments/pesapal/final-year-project/ipn');
                })
                ->andReturn('IPN-FYP-123');
            $mock->shouldReceive('submitOrder')
                ->once()
                ->withArgs(function (array $payload): bool {
                    return ($payload['id'] ?? '') !== ''
                        && ($payload['notification_id'] ?? '') === 'IPN-FYP-123'
                        && str_contains((string) ($payload['callback_url'] ?? ''), '/payments/pesapal/final-year-project/callback')
                        && (float) ($payload['amount'] ?? 0) === 50000.0;
                })
                ->andReturn([
                    'redirect_url' => 'https://pay.pesapal.com/checkout/fyp-test',
                    'order_tracking_id' => 'TRK-FYP-001',
                ]);
        });

        $response = $this->post(route('final-year-project-hosting.order.store'), [
            'full_name' => 'Student One',
            'email' => 'student1@example.com',
            'phone' => '+256700000001',
            'institution' => 'KIU',
            'system_name' => 'Clinic Records System',
            'system_repo_url' => 'https://github.com/example/clinic-records',
            'system_zip_source' => UploadedFile::fake()->create('clinic-records.zip', 120, 'application/zip'),
            'package' => 'hosting_only',
            'notes' => 'Need deployment support.',
        ]);

        $response->assertRedirect('https://pay.pesapal.com/checkout/fyp-test');

        $order = FinalYearProjectOrder::query()->firstOrFail();
        $this->assertSame('PENDING', $order->payment_status);
        $this->assertSame('TRK-FYP-001', $order->order_tracking_id);
        $this->assertSame('hosting_only', $order->package_key);
        $this->assertSame('student1@example.com', $order->customer_email);
    }
}


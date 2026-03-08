<?php

namespace App\Http\Controllers;

use App\Models\FinalYearProjectOrder;
use App\Services\PesapalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use RuntimeException;

class FinalYearProjectHostingController extends Controller
{
    public function __construct(private readonly PesapalService $pesapal)
    {
    }

    public function index(Request $request): View
    {
        $ordersReady = $this->ordersTableReady();
        $orderReference = trim((string) $request->query('order', ''));
        $activeOrder = $ordersReady && $orderReference !== ''
            ? FinalYearProjectOrder::query()->where('order_reference', $orderReference)->first()
            : null;

        if ($activeOrder && $request->boolean('refresh_payment') && filled($activeOrder->order_tracking_id) && $this->pesapal->isConfigured()) {
            try {
                $this->syncPaymentStatus($activeOrder, (string) $activeOrder->order_tracking_id);
                $activeOrder->refresh();
            } catch (RuntimeException $exception) {
                session()->flash('payment_error', $exception->getMessage());
            }
        }

        return view('pages.final-year-project-hosting', [
            'packages' => $this->packageCatalog(),
            'hostedSystems' => $this->hostedSystems(),
            'activeOrder' => $activeOrder,
            'currency' => $this->pesapal->currency(),
            'dealValidUntil' => 'December 31, 2026',
            'pesapalConfigured' => $this->pesapal->isConfigured(),
            'ordersReady' => $ordersReady,
        ]);
    }

    public function storeOrder(Request $request): RedirectResponse
    {
        if (! $this->ordersTableReady()) {
            return redirect()
                ->route('final-year-project-hosting')
                ->with('payment_error', 'Order table not ready yet. Please run php artisan migrate --force on the existing database.');
        }

        if (filled($request->input('company'))) {
            return redirect()
                ->route('final-year-project-hosting')
                ->with('payment_status', 'Order received.');
        }

        $packageKeys = array_keys($this->packageCatalog());

        $validated = $request->validate(
            [
                'full_name' => ['required', 'string', 'max:120'],
                'email' => ['required', 'email:rfc', 'max:190'],
                'phone' => ['required', 'string', 'max:40'],
                'institution' => ['nullable', 'string', 'max:160'],
                'system_name' => ['required', 'string', 'max:180'],
                'system_repo_url' => ['nullable', 'url', 'max:2048'],
                'system_zip_source' => ['required', 'file', 'mimes:zip', 'max:51200'],
                'package' => ['required', 'in:'.implode(',', $packageKeys)],
                'domain_name' => ['nullable', 'string', 'max:120'],
                'notes' => ['nullable', 'string', 'max:1200'],
            ],
            [
                'full_name.required' => 'Please enter your full name.',
                'email.required' => 'Please enter your email.',
                'phone.required' => 'Please enter your phone or WhatsApp number.',
                'system_name.required' => 'Please enter your system name.',
                'system_zip_source.required' => 'Please upload your system source ZIP file.',
                'system_zip_source.mimes' => 'The source code file must be a ZIP archive.',
                'package.required' => 'Please choose a hosting package.',
            ]
        );

        $package = $this->packageCatalog()[$validated['package']];
        $isDomainPackage = $validated['package'] === 'domain_hosting';
        $domain = trim((string) ($validated['domain_name'] ?? ''));
        $systemName = trim((string) $validated['system_name']);
        $repoUrl = trim((string) ($validated['system_repo_url'] ?? ''));

        if ($isDomainPackage && $domain === '') {
            return redirect()
                ->route('final-year-project-hosting')
                ->withInput()
                ->withErrors(['domain_name' => 'Please enter your preferred domain name.']);
        }

        $zipFile = $request->file('system_zip_source');
        $zipPath = $zipFile?->store('fyp-system-zips', ['disk' => 'local']);

        if (! is_string($zipPath) || $zipPath === '') {
            return redirect()
                ->route('final-year-project-hosting')
                ->withInput()
                ->withErrors(['system_zip_source' => 'Could not save the uploaded ZIP file. Please try again.']);
        }

        $order = FinalYearProjectOrder::query()->create([
            'order_reference' => $this->generateOrderReference(),
            'customer_name' => $validated['full_name'],
            'customer_email' => $validated['email'],
            'customer_phone' => $validated['phone'],
            'institution' => (string) ($validated['institution'] ?? ''),
            'project_title' => $systemName,
            'system_name' => $systemName,
            'system_repo_url' => $repoUrl !== '' ? $repoUrl : null,
            'source_zip_path' => $zipPath,
            'source_zip_original_name' => $zipFile?->getClientOriginalName(),
            'package_key' => $validated['package'],
            'package_label' => $package['label'],
            'domain_name' => $isDomainPackage ? $domain : null,
            'notes' => (string) ($validated['notes'] ?? ''),
            'currency' => $this->pesapal->currency(),
            'amount' => round((float) $package['price'], 2),
            'payment_status' => 'NOT_STARTED',
            'payment_status_description' => 'Not started',
        ]);

        if (! $this->pesapal->isConfigured()) {
            return redirect()
                ->route('final-year-project-hosting', ['order' => $order->order_reference])
                ->with('payment_error', 'Pesapal credentials are missing. Set PESAPAL_CONSUMER_KEY and PESAPAL_CONSUMER_SECRET in .env.');
        }

        [$firstName, $lastName] = $this->splitName((string) $order->customer_name);

        try {
            $ipnId = $this->resolveIpnId();
            $response = $this->pesapal->submitOrder([
                'id' => $order->order_reference,
                'currency' => $order->currency,
                'amount' => round((float) $order->amount, 2),
                'description' => "{$order->package_label} - {$systemName}",
                'callback_url' => $this->callbackUrl(),
                'notification_id' => $ipnId,
                'billing_address' => [
                    'email_address' => $order->customer_email,
                    'phone_number' => $order->customer_phone,
                    'country_code' => $this->pesapal->countryCode(),
                    'first_name' => $firstName,
                    'middle_name' => '',
                    'last_name' => $lastName,
                    'line_1' => 'Kitintale Road',
                    'line_2' => (string) ($order->institution ?: 'Student Project'),
                    'city' => 'Kampala',
                    'state' => 'Kampala',
                    'postal_code' => '00000',
                    'zip_code' => '00000',
                ],
            ]);
        } catch (RuntimeException $exception) {
            return redirect()
                ->route('final-year-project-hosting', ['order' => $order->order_reference])
                ->with('payment_error', $exception->getMessage());
        }

        $redirectUrl = (string) ($response['redirect_url'] ?? '');
        $trackingId = (string) ($response['order_tracking_id'] ?? '');

        if ($redirectUrl === '') {
            return redirect()
                ->route('final-year-project-hosting', ['order' => $order->order_reference])
                ->with('payment_error', 'Pesapal did not return a checkout link. Please try again.');
        }

        $order->forceFill([
            'payment_status' => 'PENDING',
            'payment_status_description' => 'Pending checkout',
            'order_tracking_id' => $trackingId,
            'pesapal_redirect_url' => $redirectUrl,
            'gateway_response' => $response,
        ])->save();

        return redirect()->away($redirectUrl);
    }

    public function paymentCallback(Request $request): RedirectResponse
    {
        if (! $this->ordersTableReady()) {
            return redirect()
                ->route('final-year-project-hosting')
                ->with('payment_error', 'Order table not ready yet. Please run php artisan migrate --force on the existing database.');
        }

        [$orderReference, $trackingId] = $this->extractPaymentReferences($request);

        if ($orderReference === '') {
            return redirect()
                ->route('final-year-project-hosting')
                ->with('payment_error', 'Payment callback did not include a valid order reference.');
        }

        $order = FinalYearProjectOrder::query()
            ->where('order_reference', $orderReference)
            ->first();

        if (! $order) {
            return redirect()
                ->route('final-year-project-hosting')
                ->with('payment_error', "Order {$orderReference} was not found.");
        }

        if ($trackingId !== '' && $this->pesapal->isConfigured()) {
            try {
                $this->syncPaymentStatus($order, $trackingId);
            } catch (RuntimeException $exception) {
                return redirect()
                    ->route('final-year-project-hosting', ['order' => $order->order_reference])
                    ->with('payment_error', $exception->getMessage());
            }
        } elseif ($trackingId !== '') {
            $order->forceFill(['order_tracking_id' => $trackingId])->save();
        }

        $order->refresh();

        return redirect()
            ->route('final-year-project-hosting', ['order' => $order->order_reference])
            ->with('payment_status', "Payment callback received. Current status: {$order->payment_status_description}.");
    }

    public function paymentIpn(Request $request): JsonResponse
    {
        if (! $this->ordersTableReady()) {
            return response()->json([
                'received' => false,
                'message' => 'Order table not ready. Run migrations.',
            ], 503);
        }

        [$orderReference, $trackingId] = $this->extractPaymentReferences($request);

        if ($orderReference === '' || $trackingId === '') {
            return response()->json([
                'received' => false,
                'message' => 'Missing order reference or tracking ID.',
            ], 422);
        }

        $order = FinalYearProjectOrder::query()
            ->where('order_reference', $orderReference)
            ->first();

        if (! $order) {
            return response()->json([
                'received' => false,
                'message' => 'Order not found.',
            ], 404);
        }

        try {
            $this->syncPaymentStatus($order, $trackingId);
        } catch (RuntimeException $exception) {
            return response()->json([
                'received' => false,
                'message' => $exception->getMessage(),
            ], 500);
        }

        $order->refresh();

        return response()->json([
            'received' => true,
            'order_reference' => $order->order_reference,
            'status' => $order->payment_status,
            'status_description' => $order->payment_status_description,
        ]);
    }

    public function orderStatus(string $orderReference): JsonResponse
    {
        if (! $this->ordersTableReady()) {
            return response()->json([
                'ok' => false,
                'message' => 'Order table not ready. Run migrations.',
            ], 503);
        }

        $order = FinalYearProjectOrder::query()
            ->where('order_reference', $orderReference)
            ->first();

        if (! $order) {
            return response()->json([
                'ok' => false,
                'message' => 'Order not found.',
            ], 404);
        }

        if (filled($order->order_tracking_id) && $this->pesapal->isConfigured()) {
            try {
                $this->syncPaymentStatus($order, (string) $order->order_tracking_id);
                $order->refresh();
            } catch (RuntimeException $exception) {
                return response()->json([
                    'ok' => false,
                    'message' => $exception->getMessage(),
                ], 500);
            }
        }

        return response()->json([
            'ok' => true,
            'order_reference' => $order->order_reference,
            'system_name' => $order->system_name ?? $order->project_title,
            'payment_status' => $order->payment_status,
            'payment_status_description' => $order->payment_status_description,
            'amount' => (float) $order->amount,
            'currency' => $order->currency,
            'tracking_id' => $order->order_tracking_id,
        ]);
    }

    private function resolveIpnId(): string
    {
        $configured = (string) config('services.pesapal.final_year_project.ipn_id', '');
        if ($configured !== '') {
            return $configured;
        }

        return $this->pesapal->resolveIpnId($this->ipnUrl());
    }

    private function callbackUrl(): string
    {
        $configured = (string) config('services.pesapal.final_year_project.callback_url', '');

        return $configured !== ''
            ? rtrim($configured, '/')
            : route('final-year-project-hosting.payment.callback');
    }

    private function ipnUrl(): string
    {
        $configured = (string) config('services.pesapal.final_year_project.ipn_url', '');

        return $configured !== ''
            ? rtrim($configured, '/')
            : route('final-year-project-hosting.payment.ipn');
    }

    private function syncPaymentStatus(FinalYearProjectOrder $order, string $trackingId): void
    {
        $payload = $this->pesapal->transactionStatus($trackingId);
        $description = (string) ($payload['payment_status_description']
            ?? $payload['status']
            ?? $payload['status_description']
            ?? 'Pending');

        $normalized = strtoupper($description);
        $status = 'PENDING';

        if (str_contains($normalized, 'COMPLETED') || str_contains($normalized, 'PAID')) {
            $status = 'PAID';
        } elseif (str_contains($normalized, 'FAILED') || str_contains($normalized, 'INVALID') || str_contains($normalized, 'REVERSED')) {
            $status = 'FAILED';
        }

        $order->forceFill([
            'payment_status' => $status,
            'payment_status_description' => $description,
            'order_tracking_id' => $trackingId,
            'gateway_status_payload' => $payload,
            'paid_at' => $status === 'PAID' && ! $order->paid_at ? now() : $order->paid_at,
        ])->save();
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function splitName(string $fullName): array
    {
        $parts = preg_split('/\s+/', trim($fullName)) ?: [];
        $firstName = $parts[0] ?? 'Customer';
        array_shift($parts);
        $lastName = count($parts) ? implode(' ', $parts) : $firstName;

        return [$firstName, $lastName];
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function extractPaymentReferences(Request $request): array
    {
        $orderReference = (string) ($request->input('OrderMerchantReference')
            ?? $request->input('merchant_reference')
            ?? $request->input('MerchantReference')
            ?? $request->input('order_reference')
            ?? '');

        $trackingId = (string) ($request->input('OrderTrackingId')
            ?? $request->input('orderTrackingId')
            ?? $request->input('order_tracking_id')
            ?? '');

        return [trim($orderReference), trim($trackingId)];
    }

    private function generateOrderReference(): string
    {
        do {
            $reference = 'FYP-'.now()->format('Ymd').'-'.random_int(1000, 9999);
        } while (FinalYearProjectOrder::query()->where('order_reference', $reference)->exists());

        return $reference;
    }

    private function ordersTableReady(): bool
    {
        return Schema::hasTable('final_year_project_orders');
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function packageCatalog(): array
    {
        return [
            'hosting_only' => [
                'label' => 'Hosting only',
                'price' => 50000,
                'summary' => 'Reliable deployment for your final year project system.',
                'includes' => [
                    'Secure hosting setup for one student project',
                    'Deployment support for Laravel/PHP apps',
                    'Technical onboarding with DNS guidance',
                    'Valid until December 2026',
                ],
            ],
            'domain_hosting' => [
                'label' => 'Domain + hosting',
                'price' => 86000,
                'summary' => 'Get both hosting and your own project domain.',
                'includes' => [
                    'Everything in Hosting only package',
                    'One domain name registration (.com where available)',
                    'Domain and DNS connection handled for you',
                    'Upgrade to full yearly hosting anytime',
                ],
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function hostedSystems(): array
    {
        return [
            [
                'name' => 'AROSOFT ERP',
                'type' => 'Enterprise System',
                'summary' => 'Finance, stock, purchasing, and operations workflows hosted with continuous uptime monitoring.',
                'stack' => ['ERPNext', 'MariaDB', 'Cloud VPS'],
                'status' => 'Live',
                'image_url' => 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=1200&q=80',
                'primary_url' => 'https://erp.arosoft.io',
                'primary_label' => 'View system',
                'secondary_url' => route('contact'),
                'secondary_label' => 'Request similar setup',
            ],
            [
                'name' => 'KIU Clinic Records',
                'type' => 'Student Project',
                'summary' => 'Role-based clinic management with patient records, pharmacy modules, and reporting dashboards.',
                'stack' => ['Laravel', 'MySQL', 'Nginx'],
                'status' => 'Hosted',
                'image_url' => 'https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?auto=format&fit=crop&w=1200&q=80',
                'primary_url' => route('contact'),
                'primary_label' => 'View case request',
                'secondary_url' => route('final-year-project-hosting').'#fyp-order-form',
                'secondary_label' => 'Host your project',
            ],
            [
                'name' => 'School Voting Platform',
                'type' => 'Student Project',
                'summary' => 'Secure election workflow with candidate management, vote auditing, and real-time tally results.',
                'stack' => ['Laravel', 'MySQL', 'Redis'],
                'status' => 'Hosted',
                'image_url' => 'https://images.unsplash.com/photo-1529107386315-e1a2ed48a620?auto=format&fit=crop&w=1200&q=80',
                'primary_url' => route('contact'),
                'primary_label' => 'View case request',
                'secondary_url' => route('final-year-project-hosting').'#fyp-order-form',
                'secondary_label' => 'Host your project',
            ],
            [
                'name' => 'Hostel Booking System',
                'type' => 'Student Project',
                'summary' => 'Room allocation, payment references, and admin reporting delivered on a student-friendly budget.',
                'stack' => ['PHP', 'MySQL', 'cPanel'],
                'status' => 'Hosted',
                'image_url' => 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?auto=format&fit=crop&w=1200&q=80',
                'primary_url' => route('contact'),
                'primary_label' => 'View case request',
                'secondary_url' => route('final-year-project-hosting').'#fyp-order-form',
                'secondary_label' => 'Host your project',
            ],
            [
                'name' => 'University eLearning',
                'type' => 'Academic Portal',
                'summary' => 'Course management, assignments, and student dashboards deployed with secure user access.',
                'stack' => ['Laravel', 'MySQL', 'Nginx'],
                'status' => 'Live',
                'image_url' => 'https://images.unsplash.com/photo-1434030216411-0b793f4b4173?auto=format&fit=crop&w=1200&q=80',
                'primary_url' => route('contact'),
                'primary_label' => 'View case request',
                'secondary_url' => route('final-year-project-hosting').'#fyp-order-form',
                'secondary_label' => 'Host your project',
            ],
            [
                'name' => 'Inventory POS Cloud',
                'type' => 'Business System',
                'summary' => 'Stock control and POS workflows running with backup routines and uptime monitoring.',
                'stack' => ['Laravel', 'MySQL', 'Redis'],
                'status' => 'Hosted',
                'image_url' => 'https://images.unsplash.com/photo-1553413077-190dd305871c?auto=format&fit=crop&w=1200&q=80',
                'primary_url' => route('contact'),
                'primary_label' => 'View case request',
                'secondary_url' => route('final-year-project-hosting').'#fyp-order-form',
                'secondary_label' => 'Host your project',
            ],
        ];
    }
}

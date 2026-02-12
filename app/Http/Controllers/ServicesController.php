<?php

namespace App\Http\Controllers;

use App\Services\PesapalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use RuntimeException;

class ServicesController extends Controller
{
    public function __construct(private readonly PesapalService $pesapal)
    {
    }

    public function index(Request $request): View
    {
        $quoteId = (string) $request->query('quote', '');
        $activeQuote = $quoteId !== '' ? $this->getQuote($quoteId) : null;

        if ($activeQuote && $request->boolean('refresh_payment') && ! empty($activeQuote['payment']['order_tracking_id']) && $this->pesapal->isConfigured()) {
            try {
                $activeQuote = $this->syncPaymentStatus($activeQuote, (string) $activeQuote['payment']['order_tracking_id']);
            } catch (RuntimeException $exception) {
                session()->flash('payment_error', $exception->getMessage());
            }
        }

        if ($activeQuote) {
            $activeQuote['whatsapp_url'] = $this->buildQuoteWhatsAppUrl($activeQuote);
        }

        return view('pages.services', [
            'serviceCatalog' => $this->serviceCatalog(),
            'packageCatalog' => $this->packageCatalog(),
            'addonCatalog' => $this->addonCatalog(),
            'timelineCatalog' => $this->timelineCatalog(),
            'activeQuote' => $activeQuote,
            'pesapalConfigured' => $this->pesapal->isConfigured(),
            'currency' => $this->pesapal->currency(),
        ]);
    }

    public function generateQuote(Request $request): RedirectResponse
    {
        if (filled($request->input('company'))) {
            return redirect()
                ->route('services')
                ->with('quote_status', 'Quote generated successfully.');
        }

        $serviceKeys = array_keys($this->serviceCatalog());
        $packageKeys = array_keys($this->packageCatalog());
        $timelineKeys = array_keys($this->timelineCatalog());
        $addonKeys = array_keys($this->addonCatalog());

        $validated = $request->validate(
            [
                'full_name' => ['required', 'string', 'max:120'],
                'email' => ['required', 'email:rfc,dns', 'max:190'],
                'phone' => ['nullable', 'string', 'max:40'],
                'company_name' => ['nullable', 'string', 'max:160'],
                'service' => ['required', 'in:'.implode(',', $serviceKeys)],
                'package' => ['required', 'in:'.implode(',', $packageKeys)],
                'scope_units' => ['nullable', 'integer', 'min:1', 'max:2000'],
                'timeline' => ['required', 'in:'.implode(',', $timelineKeys)],
                'addons' => ['nullable', 'array'],
                'addons.*' => ['in:'.implode(',', $addonKeys)],
                'notes' => ['nullable', 'string', 'max:2500'],
            ],
            [
                'full_name.required' => 'Please enter your name for the quote.',
                'email.required' => 'Please provide your email address.',
                'service.required' => 'Please select a service.',
                'package.required' => 'Please choose a package level.',
                'timeline.required' => 'Please choose your preferred timeline.',
            ]
        );

        $quote = $this->buildQuote($validated);
        $this->storeQuote($quote);

        return redirect()
            ->route('services', ['quote' => $quote['quote_id']])
            ->with('quote_status', "Quote {$quote['quote_id']} generated successfully.");
    }

    public function payQuote(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'quote_id' => ['required', 'string', 'max:40'],
            'payment_mode' => ['required', 'in:deposit,full'],
        ]);

        $quote = $this->getQuote($validated['quote_id']);
        if (! $quote) {
            return redirect()
                ->route('services')
                ->with('payment_error', 'Quote not found. Please generate a new quote.');
        }

        if (! $this->pesapal->isConfigured()) {
            return redirect()
                ->route('services', ['quote' => $quote['quote_id']])
                ->with('payment_error', 'Pesapal credentials are missing. Set PESAPAL_CONSUMER_KEY and PESAPAL_CONSUMER_SECRET in .env.');
        }

        $amount = $validated['payment_mode'] === 'full'
            ? (float) $quote['totals']['total']
            : (float) $quote['totals']['deposit'];

        [$firstName, $lastName] = $this->splitName((string) $quote['customer']['full_name']);

        try {
            $ipnId = $this->pesapal->resolveIpnId();
            $response = $this->pesapal->submitOrder([
                'id' => $quote['quote_id'],
                'currency' => $this->pesapal->currency(),
                'amount' => round($amount, 2),
                'description' => "{$quote['service']['label']} quote payment ({$validated['payment_mode']})",
                'callback_url' => $this->pesapal->callbackUrl(),
                'notification_id' => $ipnId,
                'billing_address' => [
                    'email_address' => (string) $quote['customer']['email'],
                    'phone_number' => (string) ($quote['customer']['phone'] ?? ''),
                    'country_code' => $this->pesapal->countryCode(),
                    'first_name' => $firstName,
                    'middle_name' => '',
                    'last_name' => $lastName,
                    'line_1' => 'Kitintale Road',
                    'line_2' => (string) ($quote['customer']['company_name'] ?? ''),
                    'city' => 'Kampala',
                    'state' => 'Kampala',
                    'postal_code' => '00000',
                    'zip_code' => '00000',
                ],
            ]);
        } catch (RuntimeException $exception) {
            return redirect()
                ->route('services', ['quote' => $quote['quote_id']])
                ->with('payment_error', $exception->getMessage());
        }

        $redirectUrl = (string) ($response['redirect_url'] ?? '');
        $trackingId = (string) ($response['order_tracking_id'] ?? '');

        if ($redirectUrl === '') {
            return redirect()
                ->route('services', ['quote' => $quote['quote_id']])
                ->with('payment_error', 'Pesapal did not return a checkout link. Please try again.');
        }

        $quote['payment'] = array_merge($quote['payment'], [
            'mode' => $validated['payment_mode'],
            'amount_due' => round($amount, 2),
            'status' => 'PENDING',
            'status_description' => 'Pending checkout',
            'order_tracking_id' => $trackingId,
            'redirect_url' => $redirectUrl,
            'last_attempt_at' => now()->toIso8601String(),
            'gateway_response' => $response,
        ]);

        $this->storeQuote($quote);

        return redirect()->away($redirectUrl);
    }

    public function paymentCallback(Request $request): RedirectResponse
    {
        [$quoteId, $trackingId] = $this->extractPaymentReferences($request);

        if ($quoteId === '') {
            return redirect()
                ->route('services')
                ->with('payment_error', 'Payment callback did not include a valid quote reference.');
        }

        $quote = $this->getQuote($quoteId);
        if (! $quote) {
            return redirect()
                ->route('services')
                ->with('payment_error', "Quote {$quoteId} was not found.");
        }

        if ($trackingId !== '') {
            $quote['payment']['order_tracking_id'] = $trackingId;
        }

        if ($trackingId !== '' && $this->pesapal->isConfigured()) {
            try {
                $quote = $this->syncPaymentStatus($quote, $trackingId);
            } catch (RuntimeException $exception) {
                $this->storeQuote($quote);

                return redirect()
                    ->route('services', ['quote' => $quote['quote_id']])
                    ->with('payment_error', $exception->getMessage());
            }
        } else {
            $this->storeQuote($quote);
        }

        return redirect()
            ->route('services', ['quote' => $quote['quote_id']])
            ->with('payment_status', "Payment callback received. Current status: {$quote['payment']['status_description']}.");
    }

    public function paymentIpn(Request $request): JsonResponse
    {
        [$quoteId, $trackingId] = $this->extractPaymentReferences($request);

        if ($quoteId === '' || $trackingId === '') {
            return response()->json([
                'received' => false,
                'message' => 'Missing quote reference or tracking ID.',
            ], 422);
        }

        $quote = $this->getQuote($quoteId);
        if (! $quote) {
            return response()->json([
                'received' => false,
                'message' => 'Quote not found.',
            ], 404);
        }

        try {
            $quote = $this->syncPaymentStatus($quote, $trackingId);
        } catch (RuntimeException $exception) {
            return response()->json([
                'received' => false,
                'message' => $exception->getMessage(),
            ], 500);
        }

        return response()->json([
            'received' => true,
            'quote_id' => $quote['quote_id'],
            'status' => $quote['payment']['status'],
            'status_description' => $quote['payment']['status_description'],
        ]);
    }

    public function quoteStatus(string $quoteId): JsonResponse
    {
        $quote = $this->getQuote($quoteId);
        if (! $quote) {
            return response()->json([
                'ok' => false,
                'message' => 'Quote not found.',
            ], 404);
        }

        if (! empty($quote['payment']['order_tracking_id']) && $this->pesapal->isConfigured()) {
            try {
                $quote = $this->syncPaymentStatus($quote, (string) $quote['payment']['order_tracking_id']);
            } catch (RuntimeException $exception) {
                return response()->json([
                    'ok' => false,
                    'message' => $exception->getMessage(),
                ], 500);
            }
        }

        return response()->json([
            'ok' => true,
            'quote_id' => $quote['quote_id'],
            'payment' => $quote['payment'],
            'totals' => $quote['totals'],
        ]);
    }

    private function buildQuote(array $data): array
    {
        $services = $this->serviceCatalog();
        $packages = $this->packageCatalog();
        $addons = $this->addonCatalog();
        $timelines = $this->timelineCatalog();

        $service = $services[$data['service']];
        $package = $packages[$data['package']];
        $timeline = $timelines[$data['timeline']];
        $requestedAddons = array_values($data['addons'] ?? []);
        $units = max(1, (int) ($data['scope_units'] ?? 1));

        $base = (float) $service['starting_price'];
        $packageCharge = round($base * ((float) $package['multiplier'] - 1), 2);
        $unitCharge = $this->calculateScopeCharge($data['service'], $units);

        $addonCharge = 0.0;
        $lineItems = [
            ['label' => "{$service['label']} base", 'amount' => $base],
        ];

        if ($packageCharge > 0) {
            $lineItems[] = ['label' => "{$package['label']} package uplift", 'amount' => $packageCharge];
        }

        if ($unitCharge > 0) {
            $lineItems[] = ['label' => 'Extended project scope', 'amount' => $unitCharge];
        }

        foreach ($requestedAddons as $addonKey) {
            $addon = $addons[$addonKey] ?? null;
            if (! $addon) {
                continue;
            }

            $price = (float) $addon['price'];
            $addonCharge += $price;
            $lineItems[] = ['label' => $addon['label'], 'amount' => $price];
        }

        $subTotal = $base + $packageCharge + $unitCharge + $addonCharge;
        $timelineAdjustment = round($subTotal * ((float) $timeline['multiplier'] - 1), 2);
        if ($timelineAdjustment !== 0.0) {
            $lineItems[] = ['label' => "{$timeline['label']} adjustment", 'amount' => $timelineAdjustment];
        }

        $total = round(max(50000, $subTotal + $timelineAdjustment), 2);
        $deposit = round($total * 0.40, 2);

        $quoteId = $this->generateQuoteId();

        return [
            'quote_id' => $quoteId,
            'created_at' => now()->toIso8601String(),
            'expires_at' => now()->addDays(7)->toIso8601String(),
            'currency' => $this->pesapal->currency(),
            'customer' => [
                'full_name' => $data['full_name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? '',
                'company_name' => $data['company_name'] ?? '',
            ],
            'service' => [
                'key' => $data['service'],
                'label' => $service['label'],
            ],
            'package' => [
                'key' => $data['package'],
                'label' => $package['label'],
            ],
            'timeline' => [
                'key' => $data['timeline'],
                'label' => $timeline['label'],
            ],
            'scope_units' => $units,
            'selected_addons' => $requestedAddons,
            'line_items' => $lineItems,
            'totals' => [
                'subtotal' => round($subTotal, 2),
                'timeline_adjustment' => $timelineAdjustment,
                'total' => $total,
                'deposit' => $deposit,
            ],
            'notes' => (string) ($data['notes'] ?? ''),
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
        ];
    }

    private function calculateScopeCharge(string $serviceKey, int $units): float
    {
        if (in_array($serviceKey, ['website_design', 'web_development', 'system_development'], true)) {
            return $units > 5 ? (float) (($units - 5) * 60000) : 0.0;
        }

        if ($serviceKey === 'printing') {
            return $units > 100 ? (float) (($units - 100) * 1800) : 0.0;
        }

        return 0.0;
    }

    private function syncPaymentStatus(array $quote, string $trackingId): array
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

        $quote['payment'] = array_merge($quote['payment'], [
            'status' => $status,
            'status_description' => $description,
            'order_tracking_id' => $trackingId,
            'gateway_status_payload' => $payload,
            'checked_at' => now()->toIso8601String(),
        ]);

        $this->storeQuote($quote);

        return $quote;
    }

    private function extractPaymentReferences(Request $request): array
    {
        $quoteId = (string) ($request->input('OrderMerchantReference')
            ?? $request->input('merchant_reference')
            ?? $request->input('MerchantReference')
            ?? '');

        $trackingId = (string) ($request->input('OrderTrackingId')
            ?? $request->input('orderTrackingId')
            ?? $request->input('order_tracking_id')
            ?? '');

        return [trim($quoteId), trim($trackingId)];
    }

    private function buildQuoteWhatsAppUrl(array $quote): string
    {
        $amount = number_format((float) $quote['totals']['total']);
        $message = "Hello Arosoft, I want to proceed with quote {$quote['quote_id']} ({$quote['service']['label']}) worth {$quote['currency']} {$amount}.";

        return 'https://wa.me/256787726388?text='.rawurlencode($message);
    }

    private function splitName(string $fullName): array
    {
        $parts = preg_split('/\s+/', trim($fullName)) ?: [];
        $firstName = $parts[0] ?? 'Customer';
        array_shift($parts);
        $lastName = count($parts) ? implode(' ', $parts) : $firstName;

        return [$firstName, $lastName];
    }

    private function generateQuoteId(): string
    {
        do {
            $quoteId = 'QTE-'.now()->format('Ymd').'-'.random_int(1000, 9999);
        } while (Cache::has($this->quoteCacheKey($quoteId)));

        return $quoteId;
    }

    private function storeQuote(array $quote): void
    {
        Cache::put($this->quoteCacheKey((string) $quote['quote_id']), $quote, now()->addDays(7));
    }

    private function getQuote(string $quoteId): ?array
    {
        $quote = Cache::get($this->quoteCacheKey($quoteId));

        return is_array($quote) ? $quote : null;
    }

    private function quoteCacheKey(string $quoteId): string
    {
        return "quote:{$quoteId}";
    }

    private function serviceCatalog(): array
    {
        return [
            'printing' => [
                'label' => 'Printing',
                'starting_price' => 250000,
                'summary' => 'Business cards, flyers, branded materials, and campaign print packs.',
            ],
            'website_design' => [
                'label' => 'Website Design',
                'starting_price' => 850000,
                'summary' => 'Modern UI/UX layouts, brand-consistent pages, and conversion-focused design.',
            ],
            'web_development' => [
                'label' => 'Web Development',
                'starting_price' => 1500000,
                'summary' => 'Laravel systems, integrations, dashboards, and secure production deployment.',
            ],
            'training_courses' => [
                'label' => 'Training/Courses',
                'starting_price' => 450000,
                'summary' => 'Hands-on technical training and internship-aligned learning tracks.',
            ],
            'graphics_design' => [
                'label' => 'Graphics Design',
                'starting_price' => 300000,
                'summary' => 'Professional brand assets, social media design, and visual communication.',
            ],
            'system_development' => [
                'label' => 'System Development',
                'starting_price' => 2500000,
                'summary' => 'Custom business systems for schools, institutions, and enterprise workflows.',
            ],
        ];
    }

    private function packageCatalog(): array
    {
        return [
            'starter' => [
                'label' => 'Starter',
                'multiplier' => 1.00,
                'description' => 'Essential setup with core deliverables.',
            ],
            'business' => [
                'label' => 'Business',
                'multiplier' => 1.35,
                'description' => 'Expanded scope, enhanced polish, and better rollout support.',
            ],
            'premium' => [
                'label' => 'Premium',
                'multiplier' => 1.75,
                'description' => 'High-touch execution, advanced features, and priority delivery.',
            ],
        ];
    }

    private function timelineCatalog(): array
    {
        return [
            'express' => [
                'label' => 'Express (fast delivery)',
                'multiplier' => 1.25,
            ],
            'standard' => [
                'label' => 'Standard timeline',
                'multiplier' => 1.00,
            ],
            'flexible' => [
                'label' => 'Flexible timeline',
                'multiplier' => 0.92,
            ],
        ];
    }

    private function addonCatalog(): array
    {
        return [
            'seo_setup' => [
                'label' => 'SEO setup',
                'price' => 180000,
            ],
            'content_writing' => [
                'label' => 'Content writing support',
                'price' => 220000,
            ],
            'social_kit' => [
                'label' => 'Social media asset kit',
                'price' => 160000,
            ],
            'maintenance' => [
                'label' => '3-month maintenance plan',
                'price' => 320000,
            ],
            'training' => [
                'label' => 'Team handover training',
                'price' => 200000,
            ],
        ];
    }
}


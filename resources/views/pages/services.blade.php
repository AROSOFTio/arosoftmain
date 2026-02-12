@extends('layouts.app')

@section('title', 'Services, Pricing & Quote Builder | Arosoft Innovations Ltd')
@section('meta_description', 'Explore Arosoft service packages, pricing guidance, quote generation, WhatsApp consultation, and secure Pesapal payments.')
@section('canonical', route('services'))

@section('content')
    @php
        $formatMoney = fn (float|int $amount) => number_format((float) $amount);
        $generalWhatsApp = 'https://wa.me/256787726388?text=' . rawurlencode('Hello Arosoft, I need help selecting the best service package.');
    @endphp

    @if (session('quote_status'))
        <div class="alert-success">
            {{ session('quote_status') }}
        </div>
    @endif

    @if (session('payment_status'))
        <div class="alert-success mt-4">
            {{ session('payment_status') }}
        </div>
    @endif

    @if (session('payment_error'))
        <div class="mt-4 rounded-xl border border-[color:rgba(17,24,39,0.16)] bg-[color:rgba(0,157,49,0.08)] p-4 text-sm text-[color:rgba(17,24,39,0.92)]">
            {{ session('payment_error') }}
        </div>
    @endif

    <section class="hero-surface p-8 sm:p-10 lg:p-12">
        <div class="hero-grid">
            <div>
                <p class="page-kicker">Services & Pricing</p>
                <h1 class="page-title mt-4">Powerful solutions for printing, web, systems, and training</h1>
                <p class="section-copy mt-5 max-w-3xl">
                    Build your estimate instantly, generate a quote reference, chat with our team on WhatsApp, and complete payment
                    securely via Pesapal when you are ready to proceed.
                </p>
                <div class="mt-7 flex flex-wrap gap-3">
                    <a href="#quote-builder" class="btn-solid">Generate quote</a>
                    <a href="{{ $generalWhatsApp }}" class="btn-outline" target="_blank" rel="noopener noreferrer">Chat on WhatsApp</a>
                </div>
            </div>

            <aside class="grid gap-3">
                <article class="feature-tile">
                    <h3 class="font-heading">Fast estimate flow</h3>
                    <p>Choose service, package, timeline, and add-ons to get a realistic quote instantly.</p>
                </article>
                <article class="feature-tile">
                    <h3 class="font-heading">Secure online payment</h3>
                    <p>Pay deposit or full amount through Pesapal and track payment status from your quote card.</p>
                </article>
                <article class="feature-tile">
                    <h3 class="font-heading">Human support included</h3>
                    <p>Need help deciding scope? Send your quote ID to WhatsApp and we refine it together.</p>
                </article>
            </aside>
        </div>
    </section>

    <section class="content-section">
        <h2 class="section-title">Core Service Lines</h2>
        <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($serviceCatalog as $key => $service)
                <article class="info-card">
                    <p class="page-kicker">{{ strtoupper(str_replace('_', ' ', $key)) }}</p>
                    <h3 class="font-heading mt-2 text-2xl">{{ $service['label'] }}</h3>
                    <p class="mt-2 text-sm leading-7 muted-copy">{{ $service['summary'] }}</p>
                    <p class="mt-4 text-sm font-semibold text-[var(--accent)]">
                        From {{ $currency }} {{ $formatMoney($service['starting_price']) }}
                    </p>
                </article>
            @endforeach
        </div>
    </section>

    <section class="content-section">
        <h2 class="section-title">Package Levels</h2>
        <p class="mt-3 section-copy max-w-3xl">
            Choose the package level that matches your budget and required quality level. Final pricing depends on scope, timeline, and add-ons.
        </p>

        <div class="mt-5 grid gap-4 md:grid-cols-3">
            @foreach ($packageCatalog as $package)
                <article class="info-card">
                    <h3 class="font-heading text-2xl">{{ $package['label'] }}</h3>
                    <p class="mt-2 text-sm leading-7 muted-copy">{{ $package['description'] }}</p>
                    <p class="mt-4 text-sm font-semibold text-[var(--accent)]">
                        Pricing multiplier: x{{ number_format($package['multiplier'], 2) }}
                    </p>
                </article>
            @endforeach
        </div>
    </section>

    <section class="content-section">
        <h2 class="section-title">Pricing Snapshot ({{ $currency }})</h2>
        <p class="mt-3 section-copy max-w-3xl">
            Use this as a planning guide. Your generated quote includes scope adjustments, add-ons, and timeline-based pricing.
        </p>

        <div class="mt-5 overflow-x-auto rounded-2xl border border-[color:rgba(17,24,39,0.12)] bg-white">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-[color:rgba(0,157,49,0.08)]">
                    <tr>
                        <th class="px-4 py-3 font-heading text-[color:rgba(17,24,39,0.9)]">Service</th>
                        <th class="px-4 py-3 font-heading text-[color:rgba(17,24,39,0.9)]">Starter</th>
                        <th class="px-4 py-3 font-heading text-[color:rgba(17,24,39,0.9)]">Business</th>
                        <th class="px-4 py-3 font-heading text-[color:rgba(17,24,39,0.9)]">Premium</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($serviceCatalog as $service)
                        @php
                            $starter = (float) $service['starting_price'];
                            $business = $starter * 1.35;
                            $premium = $starter * 1.75;
                        @endphp
                        <tr class="border-t border-[color:rgba(17,24,39,0.1)]">
                            <td class="px-4 py-3 font-semibold text-[color:rgba(17,24,39,0.9)]">{{ $service['label'] }}</td>
                            <td class="px-4 py-3 muted-copy">{{ $currency }} {{ $formatMoney($starter) }}</td>
                            <td class="px-4 py-3 muted-copy">{{ $currency }} {{ $formatMoney($business) }}</td>
                            <td class="px-4 py-3 muted-copy">{{ $currency }} {{ $formatMoney($premium) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <section class="content-section">
        <h2 class="section-title">How Quoting & Payment Works</h2>
        <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <article class="feature-tile">
                <h3 class="font-heading">1. Select your service</h3>
                <p>Pick service line, package, timeline, and optional add-ons to define scope clearly.</p>
            </article>
            <article class="feature-tile">
                <h3 class="font-heading">2. Generate quote ID</h3>
                <p>We instantly issue a quote reference that your team can approve and track.</p>
            </article>
            <article class="feature-tile">
                <h3 class="font-heading">3. Pay via Pesapal</h3>
                <p>Pay 40% deposit to start or complete full payment immediately through secure checkout.</p>
            </article>
            <article class="feature-tile">
                <h3 class="font-heading">4. Confirm on WhatsApp</h3>
                <p>Share your quote ID on WhatsApp for kickoff details, timelines, and final onboarding.</p>
            </article>
        </div>
    </section>

    <section id="quote-builder" class="content-section grid gap-6 lg:grid-cols-5">
        <article class="info-card lg:col-span-3">
            <h2 class="section-title">Generate a Quote</h2>
            <p class="mt-3 text-sm leading-7 muted-copy">
                Fill in your project details below. We generate a quote ID you can use for payment and WhatsApp follow-up.
            </p>

            <form action="{{ route('services.quote') }}" method="POST" class="mt-6 space-y-5">
                @csrf
                <div class="hidden" aria-hidden="true">
                    <label for="company">Company</label>
                    <input id="company" type="text" name="company" tabindex="-1" autocomplete="off">
                </div>

                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <label class="form-label" for="full_name">Full name *</label>
                        <input id="full_name" type="text" name="full_name" value="{{ old('full_name') }}" class="form-field @error('full_name') border-[color:rgba(0,157,49,0.85)] @enderror" required>
                        @error('full_name')
                            <p class="mt-2 text-sm text-[var(--accent)]">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="form-label" for="email">Email *</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-field @error('email') border-[color:rgba(0,157,49,0.85)] @enderror" required>
                        @error('email')
                            <p class="mt-2 text-sm text-[var(--accent)]">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <label class="form-label" for="phone">Phone / WhatsApp</label>
                        <input id="phone" type="text" name="phone" value="{{ old('phone') }}" class="form-field">
                    </div>
                    <div>
                        <label class="form-label" for="company_name">Company / Organization</label>
                        <input id="company_name" type="text" name="company_name" value="{{ old('company_name') }}" class="form-field">
                    </div>
                </div>

                <div class="grid gap-5 md:grid-cols-3">
                    <div>
                        <label class="form-label" for="service">Service *</label>
                        <select id="service" name="service" class="form-field @error('service') border-[color:rgba(0,157,49,0.85)] @enderror" required>
                            <option value="">Select service</option>
                            @foreach ($serviceCatalog as $key => $service)
                                <option value="{{ $key }}" @selected(old('service') === $key)>{{ $service['label'] }}</option>
                            @endforeach
                        </select>
                        @error('service')
                            <p class="mt-2 text-sm text-[var(--accent)]">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="form-label" for="package">Package *</label>
                        <select id="package" name="package" class="form-field @error('package') border-[color:rgba(0,157,49,0.85)] @enderror" required>
                            <option value="">Select package</option>
                            @foreach ($packageCatalog as $key => $package)
                                <option value="{{ $key }}" @selected(old('package') === $key)>{{ $package['label'] }}</option>
                            @endforeach
                        </select>
                        @error('package')
                            <p class="mt-2 text-sm text-[var(--accent)]">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="form-label" for="scope_units">Scope units</label>
                        <input id="scope_units" type="number" min="1" name="scope_units" value="{{ old('scope_units', 5) }}" class="form-field">
                    </div>
                </div>

                <div>
                    <label class="form-label" for="timeline">Timeline *</label>
                    <select id="timeline" name="timeline" class="form-field @error('timeline') border-[color:rgba(0,157,49,0.85)] @enderror" required>
                        <option value="">Select timeline</option>
                        @foreach ($timelineCatalog as $key => $timeline)
                            <option value="{{ $key }}" @selected(old('timeline') === $key)>{{ $timeline['label'] }}</option>
                        @endforeach
                    </select>
                    @error('timeline')
                        <p class="mt-2 text-sm text-[var(--accent)]">{{ $message }}</p>
                    @enderror
                </div>

                <fieldset>
                    <legend class="form-label">Optional add-ons</legend>
                    <div class="grid gap-3 md:grid-cols-2">
                        @foreach ($addonCatalog as $key => $addon)
                            <label class="flex items-center justify-between rounded-lg border border-[color:rgba(17,24,39,0.12)] px-3 py-2">
                                <span class="text-sm muted-copy">{{ $addon['label'] }}</span>
                                <span class="ml-3 flex items-center gap-2">
                                    <span class="text-sm font-semibold text-[var(--accent)]">{{ $currency }} {{ $formatMoney($addon['price']) }}</span>
                                    <input type="checkbox" name="addons[]" value="{{ $key }}" class="h-4 w-4" @checked(in_array($key, old('addons', []), true))>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>

                <div>
                    <label class="form-label" for="notes">Project notes</label>
                    <textarea id="notes" name="notes" rows="5" class="form-field">{{ old('notes') }}</textarea>
                </div>

                <button type="submit" class="btn-solid">Generate quote</button>
            </form>
        </article>

        <aside class="info-card lg:col-span-2">
            <h2 class="section-title">Payments & Support</h2>
            <p class="mt-3 text-sm leading-7 muted-copy">
                After generating a quote, you can pay a deposit or full amount via Pesapal and continue discussion on WhatsApp.
            </p>

            <div class="mt-5 space-y-3">
                <div class="rounded-xl border border-[color:rgba(17,24,39,0.12)] p-4">
                    <h3 class="font-heading text-lg">Pesapal Checkout</h3>
                    <p class="mt-2 text-sm leading-7 muted-copy">
                        Secure online payment for card/mobile money where available in your region.
                    </p>
                    <p class="mt-2 text-xs uppercase tracking-[0.12em] {{ $pesapalConfigured ? 'text-[var(--accent)]' : 'muted-faint' }}">
                        {{ $pesapalConfigured ? 'Configured and ready' : 'Requires Pesapal credentials in .env' }}
                    </p>
                </div>

                <div class="rounded-xl border border-[color:rgba(17,24,39,0.12)] p-4">
                    <h3 class="font-heading text-lg">WhatsApp Consultation</h3>
                    <p class="mt-2 text-sm leading-7 muted-copy">
                        Share your quote ID and requirements directly with our team.
                    </p>
                    <a href="{{ $generalWhatsApp }}" class="btn-outline mt-3" target="_blank" rel="noopener noreferrer">Open WhatsApp</a>
                </div>
            </div>

            <div class="mt-6 rounded-xl border border-[color:rgba(17,24,39,0.12)] p-4">
                <h3 class="font-heading text-lg">Need a custom package?</h3>
                <p class="mt-2 text-sm leading-7 muted-copy">
                    For large institutions and long-term contracts, generate a quote then contact us for a negotiated enterprise plan.
                </p>
            </div>
        </aside>
    </section>

    @if ($activeQuote)
        <section class="content-section">
            <article class="info-card">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="page-kicker">Generated quote</p>
                        <h2 class="section-title mt-2">Quote {{ $activeQuote['quote_id'] }}</h2>
                        <p class="mt-2 text-sm muted-copy">
                            Created {{ \Carbon\Carbon::parse($activeQuote['created_at'])->format('M d, Y H:i') }} |
                            Expires {{ \Carbon\Carbon::parse($activeQuote['expires_at'])->format('M d, Y') }}
                        </p>
                    </div>
                    <div class="rounded-lg border border-[color:rgba(17,24,39,0.14)] px-4 py-2 text-sm">
                        <span class="muted-faint">Status:</span>
                        <strong class="ml-2 text-[var(--accent)]">{{ $activeQuote['payment']['status'] }}</strong>
                    </div>
                </div>

                <div class="mt-6 grid gap-5 lg:grid-cols-2">
                    <div>
                        <h3 class="font-heading text-xl">Quote breakdown</h3>
                        <div class="mt-3 space-y-2">
                            @foreach ($activeQuote['line_items'] as $lineItem)
                                <div class="flex items-center justify-between rounded-lg border border-[color:rgba(17,24,39,0.1)] px-3 py-2 text-sm">
                                    <span class="muted-copy">{{ $lineItem['label'] }}</span>
                                    <strong>{{ $activeQuote['currency'] }} {{ $formatMoney($lineItem['amount']) }}</strong>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <h3 class="font-heading text-xl">Totals</h3>
                        <div class="mt-3 rounded-lg border border-[color:rgba(17,24,39,0.12)] p-4">
                            <div class="flex justify-between text-sm"><span class="muted-copy">Subtotal</span><strong>{{ $activeQuote['currency'] }} {{ $formatMoney($activeQuote['totals']['subtotal']) }}</strong></div>
                            <div class="mt-2 flex justify-between text-sm"><span class="muted-copy">Timeline adjustment</span><strong>{{ $activeQuote['currency'] }} {{ $formatMoney($activeQuote['totals']['timeline_adjustment']) }}</strong></div>
                            <div class="mt-2 flex justify-between text-base"><span class="font-semibold">Total</span><strong>{{ $activeQuote['currency'] }} {{ $formatMoney($activeQuote['totals']['total']) }}</strong></div>
                            <div class="mt-2 flex justify-between text-base"><span class="font-semibold">Deposit (40%)</span><strong>{{ $activeQuote['currency'] }} {{ $formatMoney($activeQuote['totals']['deposit']) }}</strong></div>
                        </div>

                        <div class="mt-4 flex flex-wrap gap-3">
                            <form action="{{ route('services.quote.pay') }}" method="POST">
                                @csrf
                                <input type="hidden" name="quote_id" value="{{ $activeQuote['quote_id'] }}">
                                <input type="hidden" name="payment_mode" value="deposit">
                                <button type="submit" class="btn-solid">Pay deposit (Pesapal)</button>
                            </form>

                            <form action="{{ route('services.quote.pay') }}" method="POST">
                                @csrf
                                <input type="hidden" name="quote_id" value="{{ $activeQuote['quote_id'] }}">
                                <input type="hidden" name="payment_mode" value="full">
                                <button type="submit" class="btn-outline">Pay full amount</button>
                            </form>
                        </div>

                        <div class="mt-3 flex flex-wrap gap-3">
                            <a href="{{ $activeQuote['whatsapp_url'] }}" class="btn-outline" target="_blank" rel="noopener noreferrer">Chat quote on WhatsApp</a>
                            <a href="{{ route('services.quote.status', ['quoteId' => $activeQuote['quote_id']]) }}" class="btn-outline" target="_blank">Check payment status</a>
                            <a href="{{ route('services', ['quote' => $activeQuote['quote_id'], 'refresh_payment' => 1]) }}" class="btn-outline">Refresh on page</a>
                        </div>

                        @if (!empty($activeQuote['payment']['order_tracking_id']))
                            <p class="mt-3 text-xs uppercase tracking-[0.12em] muted-faint">
                                Tracking ID: {{ $activeQuote['payment']['order_tracking_id'] }}
                            </p>
                        @endif
                    </div>
                </div>
            </article>
        </section>
    @endif

    <section class="content-section">
        <h2 class="section-title">FAQs</h2>
        <div class="mt-5 space-y-3">
            <details class="rounded-xl border border-[color:rgba(17,24,39,0.12)] bg-white p-4">
                <summary class="cursor-pointer font-heading text-lg">Do I have to pay full amount before work starts?</summary>
                <p class="mt-3 text-sm leading-7 muted-copy">
                    No. You can pay a 40% deposit through Pesapal to confirm kickoff. The remaining balance follows agreed milestones.
                </p>
            </details>
            <details class="rounded-xl border border-[color:rgba(17,24,39,0.12)] bg-white p-4">
                <summary class="cursor-pointer font-heading text-lg">Can I revise my quote after generation?</summary>
                <p class="mt-3 text-sm leading-7 muted-copy">
                    Yes. Generate your quote first, then send the quote ID on WhatsApp and we adjust scope before final approval.
                </p>
            </details>
            <details class="rounded-xl border border-[color:rgba(17,24,39,0.12)] bg-white p-4">
                <summary class="cursor-pointer font-heading text-lg">What payment methods are available on Pesapal?</summary>
                <p class="mt-3 text-sm leading-7 muted-copy">
                    Available methods depend on your region and account setup, commonly including card and supported mobile money options.
                </p>
            </details>
        </div>
    </section>
@endsection

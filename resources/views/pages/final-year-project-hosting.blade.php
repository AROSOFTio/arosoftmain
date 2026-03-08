@extends('layouts.app')

@section('title', 'Final Year Project Hosting Offer | AROSOFT Innovations Ltd')
@section('meta_description', 'Order AROSOFT final year project hosting with clear student pricing: UGX 50,000 hosting only or UGX 86,000 domain + hosting. Pay securely via Pesapal.')
@section('canonical', route('final-year-project-hosting'))

@section('content')
    @php
        $formatMoney = fn (float|int $amount) => number_format((float) $amount);
        $hostingOnly = $packages['hosting_only'];
        $domainHosting = $packages['domain_hosting'];
    @endphp

    @if (session('payment_status'))
        <div class="alert-success">
            {{ session('payment_status') }}
        </div>
    @endif

    @if (session('payment_error'))
        <div class="mt-4 rounded-xl border border-[color:rgba(185,28,28,0.32)] bg-[color:rgba(185,28,28,0.08)] p-4 text-sm text-[color:rgba(127,29,29,0.92)]">
            {{ session('payment_error') }}
        </div>
    @endif

    @if (!$ordersReady)
        <div class="mt-4 rounded-xl border border-[color:rgba(185,28,28,0.32)] bg-[color:rgba(185,28,28,0.08)] p-4 text-sm text-[color:rgba(127,29,29,0.92)]">
            Order setup is not completed on this existing database yet. Run <strong>php artisan migrate --force</strong> then reload this page.
        </div>
    @endif

    <div x-data="{ selectedPackage: @js(old('package', 'hosting_only')) }">
        <section class="hero-surface fyp-hero p-8 sm:p-10 lg:p-12">
            <div class="hero-grid">
                <div>
                    <p class="page-kicker">Student Offer</p>
                    <h1 class="page-title mt-4">Final Year Project Hosting</h1>
                    <p class="section-copy mt-5 max-w-3xl">
                        Reliable hosting for university final year systems with a fixed student package.
                        We support Laravel, PHP, WordPress, Python, Java, Node.js, and other web stacks.
                        Choose your plan, place your order, and pay securely through Pesapal.
                    </p>
                    <div class="mt-7 flex flex-wrap gap-3">
                        <a href="#fyp-order-form" class="btn-solid">Order now</a>
                        <a href="https://wa.me/256787726388?text={{ rawurlencode('Hello AROSOFT, I need help with final year project hosting.') }}" class="btn-outline" target="_blank" rel="noopener noreferrer">
                            WhatsApp support
                        </a>
                    </div>
                    <div class="fyp-tech-strip mt-5">
                        <span>Java</span>
                        <span>Python</span>
                        <span>WordPress</span>
                        <span>PHP</span>
                        <span>Laravel</span>
                        <span>Node.js</span>
                        <span>React/Vue</span>
                    </div>
                    <p class="mt-4 text-sm font-semibold text-[color:rgba(17,24,39,0.82)]">Offer valid until {{ $dealValidUntil }}</p>
                </div>

                <aside class="fyp-side-spotlight">
                    <p class="text-[0.68rem] uppercase tracking-[0.16em] muted-faint">Quick pricing</p>
                    <div class="mt-3 space-y-3">
                        <div class="fyp-price-bubble">
                            <p class="font-heading text-lg">Hosting only</p>
                            <p class="mt-1 text-2xl font-bold text-[var(--accent)]">{{ $currency }} {{ $formatMoney($hostingOnly['price']) }}</p>
                        </div>
                        <div class="fyp-price-bubble">
                            <p class="font-heading text-lg">Domain + hosting</p>
                            <p class="mt-1 text-2xl font-bold text-[var(--accent)]">{{ $currency }} {{ $formatMoney($domainHosting['price']) }}</p>
                        </div>
                    </div>
                </aside>
            </div>
        </section>

        <section class="content-section">
            <h2 class="section-title">Clear Pricing Packages</h2>
            <p class="mt-3 section-copy max-w-3xl">
                No hidden pricing. Click a package button and jump straight to the order form.
            </p>

            <div class="mt-5 grid gap-4 md:grid-cols-2">
                <article class="info-card fyp-plan-card">
                    <p class="page-kicker">Package 1</p>
                    <h3 class="font-heading mt-2 text-2xl">{{ $hostingOnly['label'] }}</h3>
                    <p class="mt-2 text-sm leading-7 muted-copy">{{ $hostingOnly['summary'] }}</p>
                    <ul class="list-check mt-4">
                        @foreach ($hostingOnly['includes'] as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                    <p class="fyp-price-tag mt-4">{{ $currency }} {{ $formatMoney($hostingOnly['price']) }}</p>
                    <button type="button" class="btn-solid mt-4" @click="selectedPackage = 'hosting_only'; window.location.hash = 'fyp-order-form'">Order hosting only</button>
                </article>

                <article class="info-card fyp-plan-card">
                    <p class="page-kicker">Package 2</p>
                    <h3 class="font-heading mt-2 text-2xl">{{ $domainHosting['label'] }}</h3>
                    <p class="mt-2 text-sm leading-7 muted-copy">{{ $domainHosting['summary'] }}</p>
                    <ul class="list-check mt-4">
                        @foreach ($domainHosting['includes'] as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                    <p class="fyp-price-tag mt-4">{{ $currency }} {{ $formatMoney($domainHosting['price']) }}</p>
                    <button type="button" class="btn-solid mt-4" @click="selectedPackage = 'domain_hosting'; window.location.hash = 'fyp-order-form'">Order domain + hosting</button>
                </article>
            </div>
        </section>

        <section class="content-section">
            <h2 class="section-title">Offer Highlights</h2>
            <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <article class="feature-tile">
                    <h3 class="font-heading">Student budget fit</h3>
                    <p>Fixed price tiers built for final year teams and individual students.</p>
                </article>
                <article class="feature-tile">
                    <h3 class="font-heading">Fast onboarding</h3>
                    <p>We help you connect your project and complete launch setup quickly.</p>
                </article>
                <article class="feature-tile">
                    <h3 class="font-heading">Secure payment</h3>
                    <p>Checkout is handled by Pesapal for mobile money and card options where available.</p>
                </article>
                <article class="feature-tile">
                    <h3 class="font-heading">Upgrade anytime</h3>
                    <p>Move from student package to full yearly hosting whenever you are ready.</p>
                </article>
            </div>
        </section>

        <section class="content-section">
            <h2 class="section-title">Supported Tech Stacks</h2>
            <p class="mt-3 section-copy max-w-3xl">
                This hosting offer is not limited to Laravel or PHP. We also deploy and support Java, Python, WordPress, Node.js, and modern frontend stacks.
            </p>
            <div class="fyp-tech-grid mt-5">
                <article class="feature-tile">
                    <h3 class="font-heading">Backend</h3>
                    <p>PHP, Laravel, Node.js, Java, Python.</p>
                </article>
                <article class="feature-tile">
                    <h3 class="font-heading">CMS</h3>
                    <p>WordPress and custom PHP CMS projects.</p>
                </article>
                <article class="feature-tile">
                    <h3 class="font-heading">Frontend</h3>
                    <p>React, Vue, Blade, and static web apps.</p>
                </article>
            </div>
        </section>

        <section id="fyp-order-form" class="content-section grid gap-6 lg:grid-cols-5">
            <article class="info-card lg:col-span-3">
                <h2 class="section-title">Place Your Order</h2>
                <p class="mt-3 text-sm leading-7 muted-copy">
                    Submit your project details and you will be redirected to Pesapal checkout.
                </p>

                <form action="{{ route('final-year-project-hosting.order.store') }}" method="POST" enctype="multipart/form-data" class="mt-6 space-y-5">
                    @csrf
                    <div class="hidden" aria-hidden="true">
                        <label for="company">Company</label>
                        <input id="company" type="text" name="company" tabindex="-1" autocomplete="off">
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label class="form-label" for="full_name">Full name *</label>
                            <input id="full_name" type="text" name="full_name" value="{{ old('full_name') }}" class="form-field @error('full_name') border-[color:rgba(185,28,28,0.6)] @enderror" required>
                            @error('full_name')
                                <p class="mt-2 text-sm text-[color:rgba(127,29,29,0.95)]">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="form-label" for="email">Email *</label>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-field @error('email') border-[color:rgba(185,28,28,0.6)] @enderror" required>
                            @error('email')
                                <p class="mt-2 text-sm text-[color:rgba(127,29,29,0.95)]">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label class="form-label" for="phone">Phone / WhatsApp *</label>
                            <input id="phone" type="text" name="phone" value="{{ old('phone') }}" class="form-field @error('phone') border-[color:rgba(185,28,28,0.6)] @enderror" required>
                            @error('phone')
                                <p class="mt-2 text-sm text-[color:rgba(127,29,29,0.95)]">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="form-label" for="institution">Institution</label>
                            <input id="institution" type="text" name="institution" value="{{ old('institution', 'KIU') }}" class="form-field">
                        </div>
                    </div>

                    <div>
                        <label class="form-label" for="system_name">System name *</label>
                        <input id="system_name" type="text" name="system_name" value="{{ old('system_name') }}" class="form-field @error('system_name') border-[color:rgba(185,28,28,0.6)] @enderror" placeholder="Example: Student Result Management System" required>
                        @error('system_name')
                            <p class="mt-2 text-sm text-[color:rgba(127,29,29,0.95)]">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="form-label" for="system_repo_url">System repo (optional)</label>
                        <input id="system_repo_url" type="url" name="system_repo_url" value="{{ old('system_repo_url') }}" class="form-field @error('system_repo_url') border-[color:rgba(185,28,28,0.6)] @enderror" placeholder="https://github.com/username/project">
                        @error('system_repo_url')
                            <p class="mt-2 text-sm text-[color:rgba(127,29,29,0.95)]">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="form-label" for="system_zip_source">System ZIP source code *</label>
                        <input id="system_zip_source" type="file" name="system_zip_source" accept=".zip,application/zip" class="form-field @error('system_zip_source') border-[color:rgba(185,28,28,0.6)] @enderror" required>
                        <p class="mt-2 text-xs muted-faint">Upload your latest source ZIP (max 50MB).</p>
                        @error('system_zip_source')
                            <p class="mt-2 text-sm text-[color:rgba(127,29,29,0.95)]">{{ $message }}</p>
                        @enderror
                    </div>

                    <fieldset>
                        <legend class="form-label">Choose package *</legend>
                        <div class="grid gap-3 md:grid-cols-2">
                            <label class="rounded-xl border p-4 transition" :class="selectedPackage === 'hosting_only' ? 'border-[color:rgba(0,157,49,0.6)] bg-[color:rgba(0,157,49,0.08)]' : 'border-[color:rgba(17,24,39,0.14)]'">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="font-heading text-lg">{{ $hostingOnly['label'] }}</p>
                                        <p class="text-sm muted-copy">{{ $currency }} {{ $formatMoney($hostingOnly['price']) }}</p>
                                    </div>
                                    <input type="radio" name="package" value="hosting_only" x-model="selectedPackage" @checked(old('package', 'hosting_only') === 'hosting_only')>
                                </div>
                            </label>
                            <label class="rounded-xl border p-4 transition" :class="selectedPackage === 'domain_hosting' ? 'border-[color:rgba(0,157,49,0.6)] bg-[color:rgba(0,157,49,0.08)]' : 'border-[color:rgba(17,24,39,0.14)]'">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="font-heading text-lg">{{ $domainHosting['label'] }}</p>
                                        <p class="text-sm muted-copy">{{ $currency }} {{ $formatMoney($domainHosting['price']) }}</p>
                                    </div>
                                    <input type="radio" name="package" value="domain_hosting" x-model="selectedPackage" @checked(old('package') === 'domain_hosting')>
                                </div>
                            </label>
                        </div>
                        @error('package')
                            <p class="mt-2 text-sm text-[color:rgba(127,29,29,0.95)]">{{ $message }}</p>
                        @enderror
                    </fieldset>

                    <div x-show="selectedPackage === 'domain_hosting'" x-transition>
                        <label class="form-label" for="domain_name">Preferred domain name *</label>
                        <input id="domain_name" type="text" name="domain_name" value="{{ old('domain_name') }}" class="form-field @error('domain_name') border-[color:rgba(185,28,28,0.6)] @enderror" placeholder="example: myproject.com">
                        @error('domain_name')
                            <p class="mt-2 text-sm text-[color:rgba(127,29,29,0.95)]">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="form-label" for="notes">Extra notes</label>
                        <textarea id="notes" name="notes" rows="4" class="form-field">{{ old('notes') }}</textarea>
                    </div>

                    <button type="submit" class="btn-solid" @disabled(!$ordersReady)>{{ $ordersReady ? 'Order using Pesapal' : 'Order setup pending' }}</button>
                </form>
            </article>

            <aside class="info-card lg:col-span-2">
                <h2 class="section-title">Checkout & Support</h2>
                <p class="mt-3 text-sm leading-7 muted-copy">
                    Every order is saved and assigned a reference. You can refresh status after payment callback.
                </p>
                <div class="mt-4 rounded-xl border border-[color:rgba(0,157,49,0.34)] bg-[color:rgba(0,157,49,0.08)] px-4 py-3">
                    <p class="text-sm font-semibold text-[color:rgba(0,122,43,0.95)]">
                        After successful payment and valid source handover, your system goes live within a maximum of 6 hours.
                    </p>
                </div>

                <div class="mt-5 space-y-3">
                    <div class="rounded-xl border border-[color:rgba(17,24,39,0.12)] p-4">
                        <h3 class="font-heading text-lg">Need urgent help?</h3>
                        <p class="mt-2 text-sm leading-7 muted-copy">Send your project details and we help you pick the correct package quickly.</p>
                        <a href="https://wa.me/256787726388?text={{ rawurlencode('Hello AROSOFT, I need final year project hosting help.') }}" class="btn-outline mt-3" target="_blank" rel="noopener noreferrer">Chat on WhatsApp</a>
                    </div>
                </div>
            </aside>
        </section>

        @if ($activeOrder)
            <section class="content-section">
                <article class="info-card">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <p class="page-kicker">Latest order</p>
                            <h2 class="section-title mt-2">{{ $activeOrder->order_reference }}</h2>
                            <p class="mt-2 text-sm muted-copy">
                                {{ $activeOrder->package_label }} |
                                {{ $activeOrder->currency }} {{ $formatMoney($activeOrder->amount) }}
                            </p>
                            <p class="mt-2 text-sm muted-copy">
                                System: {{ $activeOrder->system_name ?? $activeOrder->project_title }}
                            </p>
                            @if ($activeOrder->system_repo_url)
                                <p class="mt-1 text-sm">
                                    <a href="{{ $activeOrder->system_repo_url }}" class="underline decoration-[color:rgba(0,157,49,0.48)]" target="_blank" rel="noopener noreferrer">Open submitted repo</a>
                                </p>
                            @endif
                            @if ($activeOrder->source_zip_original_name)
                                <p class="mt-1 text-xs uppercase tracking-[0.1em] muted-faint">
                                    ZIP uploaded: {{ $activeOrder->source_zip_original_name }}
                                </p>
                            @endif
                        </div>
                        <div class="rounded-lg border border-[color:rgba(17,24,39,0.14)] px-4 py-2 text-sm">
                            <span class="muted-faint">Status:</span>
                            <strong class="ml-2 text-[var(--accent)]">{{ $activeOrder->payment_status }}</strong>
                        </div>
                    </div>

                    <div class="mt-5 flex flex-wrap gap-3">
                        <a href="{{ route('final-year-project-hosting.order.status', ['orderReference' => $activeOrder->order_reference]) }}" class="btn-outline" target="_blank">Check payment status</a>
                        <a href="{{ route('final-year-project-hosting', ['order' => $activeOrder->order_reference, 'refresh_payment' => 1]) }}" class="btn-outline">Refresh on page</a>
                        @if ($activeOrder->pesapal_redirect_url)
                            <a href="{{ $activeOrder->pesapal_redirect_url }}" class="btn-solid">Resume Pesapal checkout</a>
                        @endif
                    </div>

                    @if ($activeOrder->order_tracking_id)
                        <p class="mt-3 text-xs uppercase tracking-[0.12em] muted-faint">
                            Tracking ID: {{ $activeOrder->order_tracking_id }}
                        </p>
                    @endif
                </article>
            </section>
        @endif

        <section class="content-section">
            <h2 class="section-title">Frequently Asked Questions</h2>
            <div class="mt-5 space-y-3">
                <details class="rounded-xl border border-[color:rgba(17,24,39,0.12)] bg-white p-4">
                    <summary class="cursor-pointer font-heading text-lg">Can we start with hosting only and add a domain later?</summary>
                    <p class="mt-3 text-sm leading-7 muted-copy">
                        Yes. You can start with the UGX 50,000 hosting package and upgrade anytime.
                    </p>
                </details>
                <details class="rounded-xl border border-[color:rgba(17,24,39,0.12)] bg-white p-4">
                    <summary class="cursor-pointer font-heading text-lg">Is this package limited to one school?</summary>
                    <p class="mt-3 text-sm leading-7 muted-copy">
                        The offer is designed for final year students and teams. Include your institution in the order form.
                    </p>
                </details>
                <details class="rounded-xl border border-[color:rgba(17,24,39,0.12)] bg-white p-4">
                    <summary class="cursor-pointer font-heading text-lg">What happens after payment?</summary>
                    <p class="mt-3 text-sm leading-7 muted-copy">
                        Your payment status is updated from Pesapal callback/IPN, then the hosting team contacts you for onboarding.
                    </p>
                </details>
            </div>
        </section>

    </div>
@endsection

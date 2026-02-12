@extends('layouts.app')

@section('title', 'Tools | Arosoft Innovations Ltd')
@section('meta_description', 'Explore Arosoft tools in one place, including quote generation, payment support, and workflow utilities.')
@section('canonical', route('tools'))

@section('content')
    @php
        $tools = [
            [
                'id' => 'service-quote-builder',
                'name' => 'Service Quote Builder',
                'status' => 'Live',
                'summary' => 'Generate a fast project estimate by selecting service, package, timeline, and optional add-ons.',
                'action_label' => 'Open quote builder',
                'action_url' => route('services') . '#quote-builder',
            ],
            [
                'id' => 'pricing-snapshot',
                'name' => 'Pricing Snapshot',
                'status' => 'Live',
                'summary' => 'Review starter, business, and premium pricing guidance before requesting a final quote.',
                'action_label' => 'View pricing tables',
                'action_url' => route('services'),
            ],
            [
                'id' => 'pesapal-checkout',
                'name' => 'Pesapal Checkout',
                'status' => 'Live',
                'summary' => 'Pay deposit or full project amount through the secure payment flow attached to generated quotes.',
                'action_label' => 'Open payment flow',
                'action_url' => route('services'),
            ],
            [
                'id' => 'payment-status-checker',
                'name' => 'Payment Status Checker',
                'status' => 'Live',
                'summary' => 'Track quote payment state and refresh confirmation details after checkout.',
                'action_label' => 'Open status tool',
                'action_url' => route('services'),
            ],
            [
                'id' => 'whatsapp-assistant',
                'name' => 'WhatsApp Scope Assistant',
                'status' => 'Live',
                'summary' => 'Send a quote reference to our team and finalize scope, deliverables, and kickoff details.',
                'action_label' => 'Start WhatsApp chat',
                'action_url' => 'https://wa.me/256787726388?text=' . rawurlencode('Hello Arosoft, I need help with a tool or quote.'),
            ],
            [
                'id' => 'client-brief-intake',
                'name' => 'Client Brief Intake',
                'status' => 'Coming soon',
                'summary' => 'Structured intake form for collecting project requirements and routing requests to the right team.',
                'action_label' => null,
                'action_url' => null,
            ],
        ];
    @endphp

    <section class="hero-surface p-8 sm:p-10 lg:p-12">
        <div class="hero-grid">
            <div>
                <p class="page-kicker">Tools Hub</p>
                <h1 class="page-title mt-4">Central index for all Arosoft tools</h1>
                <p class="section-copy mt-5 max-w-3xl">
                    This page is the base landing page for every Arosoft tool. Use the sidebar to jump to any tool and launch live
                    workflows from one location.
                </p>
                <div class="mt-7 flex flex-wrap gap-3">
                    <a href="#tools-index" class="btn-solid">Browse tools</a>
                    <a href="{{ route('contact') }}" class="btn-outline">Request a new tool</a>
                </div>
            </div>

            <aside class="grid gap-3">
                <article class="feature-tile">
                    <h3 class="font-heading">Single entry point</h3>
                    <p>All current and upcoming tools are indexed here for quick access.</p>
                </article>
                <article class="feature-tile">
                    <h3 class="font-heading">Sidebar-first navigation</h3>
                    <p>Use the sidebar list to jump directly to each tool section.</p>
                </article>
                <article class="feature-tile">
                    <h3 class="font-heading">Clear tool status</h3>
                    <p>Each tool shows whether it is live now or scheduled as coming soon.</p>
                </article>
            </aside>
        </div>
    </section>

    <section id="tools-index" class="content-section grid gap-6 lg:grid-cols-4">
        <aside class="info-card h-fit lg:sticky lg:top-28">
            <p class="page-kicker">Tools Sidebar</p>
            <h2 class="font-heading mt-2 text-2xl">All tools</h2>
            <p class="mt-3 text-sm leading-7 muted-copy">Select a tool to jump to its details and launch link.</p>

            <nav class="mt-5 space-y-2">
                @foreach ($tools as $tool)
                    <a
                        href="#{{ $tool['id'] }}"
                        class="flex items-center justify-between rounded-lg border border-[color:rgba(17,24,39,0.12)] px-3 py-2 text-sm transition hover:border-[color:rgba(0,157,49,0.45)] hover:bg-[color:rgba(0,157,49,0.08)]"
                    >
                        <span class="font-semibold text-[color:rgba(17,24,39,0.9)]">{{ $tool['name'] }}</span>
                        <span class="text-[0.62rem] uppercase tracking-[0.12em] {{ $tool['status'] === 'Live' ? 'text-[var(--accent)]' : 'muted-faint' }}">
                            {{ $tool['status'] }}
                        </span>
                    </a>
                @endforeach
            </nav>
        </aside>

        <div class="space-y-5 lg:col-span-3">
            @foreach ($tools as $tool)
                <article id="{{ $tool['id'] }}" class="info-card scroll-mt-36">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="page-kicker">Tool {{ $loop->iteration }}</p>
                            <h3 class="font-heading mt-2 text-2xl">{{ $tool['name'] }}</h3>
                        </div>
                        <span class="rounded-full border px-3 py-1 text-[0.65rem] uppercase tracking-[0.12em] {{ $tool['status'] === 'Live' ? 'border-[color:rgba(0,157,49,0.38)] text-[var(--accent)]' : 'border-[color:rgba(17,24,39,0.2)] muted-faint' }}">
                            {{ $tool['status'] }}
                        </span>
                    </div>

                    <p class="mt-4 text-sm leading-7 muted-copy">{{ $tool['summary'] }}</p>

                    <div class="mt-5 flex flex-wrap gap-3">
                        @if ($tool['action_url'])
                            <a
                                href="{{ $tool['action_url'] }}"
                                class="btn-outline"
                                @if (str_starts_with($tool['action_url'], 'https://'))
                                    target="_blank" rel="noopener noreferrer"
                                @endif
                            >
                                {{ $tool['action_label'] }}
                            </a>
                        @else
                            <span class="btn-outline cursor-not-allowed opacity-70" aria-disabled="true">Coming soon</span>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    </section>
@endsection

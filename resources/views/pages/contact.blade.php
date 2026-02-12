@extends('layouts.app')

@section('title', 'Contact Arosoft Innovations Ltd | IT and Printing Support in Kampala')
@section('meta_description', 'Contact Arosoft Innovations Ltd on Kitintale Road, Kampala. Send inquiries for IT services, printing, training, graphics design, website development, and system development.')
@section('canonical', route('contact'))
@section('schema')
    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "ContactPage",
            "name": "Contact Arosoft Innovations Ltd",
            "url": "https://arosoft.io/contact",
            "mainEntity": {
                "@type": "Organization",
                "name": "Arosoft Innovations Ltd",
                "email": "info@arosoft.io",
                "telephone": "+256787726388",
                "address": "Kitintale Road, Opposite St. Johns C.O.U, next to Sir Appolo Kaggwa St. School"
            }
        }
    </script>
@endsection

@section('content')
    <section class="shell-card rounded-3xl p-8 sm:p-10 lg:p-12">
        <p class="page-kicker">Contact Arosoft Innovations Ltd</p>
        <h1 class="page-title mt-4">Let us discuss your project requirements</h1>
        <p class="section-copy mt-5 max-w-4xl">
            We support organizations in Kampala and beyond with IT services, printing services, graphics design, website design and development,
            system development, and IT training/internship. Send us your request and our team will respond promptly.
        </p>
    </section>

    <section class="content-section grid gap-4 lg:grid-cols-3">
        <article class="info-card">
            <p class="page-kicker">Email</p>
            <h2 class="font-heading mt-2 text-xl text-[var(--ink)]">info@arosoft.io</h2>
            <p class="mt-2 text-sm leading-7 muted-copy">Best for quotes, proposals, and formal communication.</p>
        </article>
        <article class="info-card">
            <p class="page-kicker">Phone / WhatsApp</p>
            <h2 class="font-heading mt-2 text-xl text-[var(--ink)]">+256787726388</h2>
            <p class="mt-2 text-sm leading-7 muted-copy">Quick support, urgent follow-up, and project clarifications.</p>
        </article>
        <article class="info-card">
            <p class="page-kicker">Address</p>
            <h2 class="font-heading mt-2 text-xl text-[var(--ink)]">Kitintale Road</h2>
            <p class="mt-2 text-sm leading-7 muted-copy">Opposite St. Johns C.O.U, next to Sir Appolo Kaggwa St. School.</p>
        </article>
    </section>

    <section class="content-section">
        <a href="https://wa.me/256787726388" class="btn-solid" target="_blank" rel="noopener noreferrer">Chat on WhatsApp</a>
    </section>

    <section class="content-section grid gap-6 lg:grid-cols-5">
        <div class="info-card lg:col-span-3">
            <h2 class="section-title">Send Us a Message</h2>
            <p class="mt-2 text-sm leading-7 muted-copy">All fields marked required must be completed.</p>

            @if (session('contact_status'))
                <div class="alert-success mt-5">
                    {{ session('contact_status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mt-5 rounded-xl border border-[color:rgba(19,242,198,0.28)] bg-[color:rgba(19,242,198,0.08)] p-4 text-sm">
                    <p class="font-semibold">Please correct the highlighted fields and try again.</p>
                </div>
            @endif

            <form action="{{ route('contact.send') }}" method="POST" class="mt-6 space-y-5">
                @csrf

                <div class="hidden" aria-hidden="true">
                    <label for="company">Company</label>
                    <input id="company" type="text" name="company" tabindex="-1" autocomplete="off">
                </div>

                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <label for="full_name" class="form-label">Full name *</label>
                        <input id="full_name" name="full_name" type="text" value="{{ old('full_name') }}" class="form-field @error('full_name') border-[color:rgba(255,255,255,0.65)] @enderror" required>
                        @error('full_name')
                            <p class="mt-2 text-sm text-[var(--accent)]">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="email" class="form-label">Email *</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" class="form-field @error('email') border-[color:rgba(255,255,255,0.65)] @enderror" required>
                        @error('email')
                            <p class="mt-2 text-sm text-[var(--accent)]">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <label for="phone" class="form-label">Phone (optional)</label>
                        <input id="phone" name="phone" type="text" value="{{ old('phone') }}" class="form-field">
                        @error('phone')
                            <p class="mt-2 text-sm text-[var(--accent)]">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="subject" class="form-label">Subject *</label>
                        <input id="subject" name="subject" type="text" value="{{ old('subject') }}" class="form-field @error('subject') border-[color:rgba(255,255,255,0.65)] @enderror" required>
                        @error('subject')
                            <p class="mt-2 text-sm text-[var(--accent)]">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="message" class="form-label">Message *</label>
                    <textarea id="message" name="message" rows="6" class="form-field @error('message') border-[color:rgba(255,255,255,0.65)] @enderror" required>{{ old('message') }}</textarea>
                    @error('message')
                        <p class="mt-2 text-sm text-[var(--accent)]">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="btn-solid">Send message</button>
            </form>
        </div>

        <aside class="info-card lg:col-span-2">
            <h2 class="section-title">Direct Contact Details</h2>
            <dl class="mt-5 space-y-4 text-sm leading-7 muted-copy">
                <div>
                    <dt class="page-kicker">Company</dt>
                    <dd>Arosoft Innovations Ltd</dd>
                </div>
                <div>
                    <dt class="page-kicker">Address</dt>
                    <dd>Kitintale Road, Opposite St. Johns C.O.U, next to Sir Appolo Kaggwa St. School</dd>
                </div>
                <div>
                    <dt class="page-kicker">Email</dt>
                    <dd><a href="mailto:info@arosoft.io" class="text-[var(--accent)] underline decoration-transparent transition hover:decoration-[var(--accent)]">info@arosoft.io</a></dd>
                </div>
                <div>
                    <dt class="page-kicker">Phone / WhatsApp</dt>
                    <dd><a href="https://wa.me/256787726388" class="text-[var(--accent)] underline decoration-transparent transition hover:decoration-[var(--accent)]">+256787726388</a></dd>
                </div>
                <div>
                    <dt class="page-kicker">Contact URL</dt>
                    <dd><a href="https://arosoft.io/contact" class="text-[var(--accent)] underline decoration-transparent transition hover:decoration-[var(--accent)]">https://arosoft.io/contact</a></dd>
                </div>
            </dl>

            <div class="mt-8 rounded-xl border border-[color:rgba(19,242,198,0.22)] p-4">
                <h3 class="font-heading text-lg text-[var(--ink)]">Response time</h3>
                <p class="mt-2 text-sm leading-7 muted-copy">Most inquiries receive a response within one business day.</p>
            </div>
        </aside>
    </section>
@endsection

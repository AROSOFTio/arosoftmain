@extends('layouts.app')

@section('title', 'Privacy Policy | Arosoft Innovations Ltd')
@section('meta_description', 'Read the privacy policy of Arosoft Innovations Ltd, including what data we collect, how we use it, retention practices, and your rights.')
@section('canonical', route('privacy'))

@section('content')
    <section class="shell-card rounded-3xl p-8 sm:p-10 lg:p-12">
        <p class="page-kicker">Privacy Policy</p>
        <h1 class="page-title mt-4">How Arosoft Innovations Ltd handles your information</h1>
        <p class="section-copy mt-5 max-w-4xl">
            This Privacy Policy explains how Arosoft Innovations Ltd collects, uses, and protects personal information when you interact
            with our website and services.
        </p>
    </section>

    <section class="content-section space-y-5">
        <article class="info-card">
            <h2 class="section-title">1. Information We Collect</h2>
            <p class="section-copy mt-3">
                We collect information you submit through our contact form, including your name, email address, phone number (optional),
                subject, and message. We may also collect basic technical/analytics information such as page visits and browser data to
                help improve site performance.
            </p>
        </article>

        <article class="info-card">
            <h2 class="section-title">2. How We Use Your Information</h2>
            <p class="section-copy mt-3">
                We use submitted information to respond to inquiries, provide requested services, follow up on support requests, and improve
                our service delivery. We do not use your data for unrelated marketing without a lawful basis.
            </p>
        </article>

        <article class="info-card">
            <h2 class="section-title">3. Data Retention</h2>
            <p class="section-copy mt-3">
                We retain contact and support communications only for as long as reasonably necessary for business operations, legal obligations,
                dispute resolution, or service continuity.
            </p>
        </article>

        <article class="info-card">
            <h2 class="section-title">4. Data Sharing</h2>
            <p class="section-copy mt-3">
                We do not sell your personal data. We may share limited data with trusted service providers (for example hosting, email, or analytics
                infrastructure) only where required to operate our services effectively and securely.
            </p>
        </article>

        <article class="info-card">
            <h2 class="section-title">5. Security</h2>
            <p class="section-copy mt-3">
                We use reasonable technical and organizational safeguards to protect your information. While no system is fully risk-free, we continuously
                improve our security practices to reduce unauthorized access and misuse.
            </p>
        </article>

        <article class="info-card">
            <h2 class="section-title">6. Your Rights</h2>
            <p class="section-copy mt-3">
                You may request access, correction, or deletion of personal information you submitted to us. You may also ask questions about how your data
                is processed.
            </p>
        </article>

        <article class="info-card">
            <h2 class="section-title">7. Contact for Privacy Questions</h2>
            <p class="section-copy mt-3">
                Email: <a href="mailto:info@arosoft.io" class="text-[var(--accent)] underline decoration-transparent transition hover:decoration-[var(--accent)]">info@arosoft.io</a><br>
                Address: Kitintale Road, Opposite St. Johns C.O.U, next to Sir Appolo Kaggwa St. School
            </p>
        </article>
    </section>
@endsection

@extends('layouts.app')

@section('title', 'About Arosoft Innovations Ltd | IT Services, Printing, and Training in Kampala')
@section('meta_description', 'Arosoft Innovations Ltd is an IT and creative services company on Kitintale Road, Kampala, delivering IT services, printing, graphics design, website and system development, and training.')
@section('canonical', route('about'))

@section('faq_schema')
    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "FAQPage",
            "mainEntity": [
                {
                    "@type": "Question",
                    "name": "What services does Arosoft Innovations Ltd provide?",
                    "acceptedAnswer": {
                        "@type": "Answer",
                        "text": "We provide IT services, printing services, IT training and internship programs, graphics design, website design and development, and system development."
                    }
                },
                {
                    "@type": "Question",
                    "name": "Where is Arosoft Innovations Ltd located?",
                    "acceptedAnswer": {
                        "@type": "Answer",
                        "text": "We are located on Kitintale Road, Opposite St. Johns C.O.U, next to Sir Appolo Kaggwa St. School, Kampala."
                    }
                },
                {
                    "@type": "Question",
                    "name": "How can I contact Arosoft Innovations Ltd?",
                    "acceptedAnswer": {
                        "@type": "Answer",
                        "text": "You can reach us at info@arosoft.io or via WhatsApp on +256787726388 through https://arosoft.io/contact."
                    }
                }
            ]
        }
    </script>
@endsection

@section('content')
    <section class="shell-card rounded-3xl p-8 sm:p-10 lg:p-12">
        <p class="page-kicker">About Arosoft Innovations Ltd</p>
        <h1 class="page-title mt-4">Professional technology and creative services for growing organizations</h1>
        <p class="section-copy mt-5 max-w-4xl">
            Arosoft Innovations Ltd is a practical service company based on Kitintale Road, Kampala. We partner with businesses,
            schools, and institutions that need reliable delivery in technology, branding, and digital systems. Our focus is clear:
            build quality solutions, support clients consistently, and deliver measurable value.
        </p>
    </section>

    <section class="content-section grid gap-6 lg:grid-cols-2">
        <article class="info-card">
            <h2 class="section-title">Who We Are</h2>
            <p class="section-copy mt-3">
                We are a multidisciplinary team of developers, designers, trainers, and production professionals. Our combined experience
                allows us to support clients from concept to final delivery without fragmented handoffs.
            </p>
        </article>
        <article class="info-card">
            <h2 class="section-title">What We Do</h2>
            <p class="section-copy mt-3">
                We provide IT Services, Printing Services, IT Training/Internship, Graphics Design, Website Design & Development, and
                System Development. Explore our <a href="{{ route('services') }}" class="text-[var(--accent)] underline decoration-transparent transition hover:decoration-[var(--accent)]">services page</a>,
                <a href="{{ route('tutorials') }}" class="text-[var(--accent)] underline decoration-transparent transition hover:decoration-[var(--accent)]">tutorials</a>,
                and <a href="{{ route('blog') }}" class="text-[var(--accent)] underline decoration-transparent transition hover:decoration-[var(--accent)]">blog</a>.
            </p>
        </article>
    </section>

    <section class="content-section">
        <h2 class="section-title">Services Snapshot</h2>
        <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-5">
            <article class="info-card">
                <h3 class="font-heading text-lg text-[var(--ink)]">Printing</h3>
                <p class="mt-2 text-sm leading-7 muted-copy">Business cards, branded materials, and high-quality print delivery for campaigns.</p>
            </article>
            <article class="info-card">
                <h3 class="font-heading text-lg text-[var(--ink)]">Website Design</h3>
                <p class="mt-2 text-sm leading-7 muted-copy">Modern layouts and user-friendly interfaces built for credibility and conversions.</p>
            </article>
            <article class="info-card">
                <h3 class="font-heading text-lg text-[var(--ink)]">Web Development</h3>
                <p class="mt-2 text-sm leading-7 muted-copy">Robust Laravel and modern web applications tailored to your workflow and scale.</p>
            </article>
            <article class="info-card">
                <h3 class="font-heading text-lg text-[var(--ink)]">Graphics Design</h3>
                <p class="mt-2 text-sm leading-7 muted-copy">Visual identity systems, social assets, and branded communication materials.</p>
            </article>
            <article class="info-card">
                <h3 class="font-heading text-lg text-[var(--ink)]">Training/Courses</h3>
                <p class="mt-2 text-sm leading-7 muted-copy">Structured training and internship support for practical, job-ready technical skills.</p>
            </article>
        </div>
    </section>

    <section class="content-section grid gap-6 lg:grid-cols-2">
        <article class="info-card">
            <h2 class="section-title">Our Values</h2>
            <ul class="list-check mt-3">
                <li>Quality delivery standards across every service line.</li>
                <li>Reliability in communication, timelines, and after-sales support.</li>
                <li>Innovation focused on practical outcomes, not buzzwords.</li>
                <li>Customer support that remains available after launch.</li>
            </ul>
        </article>
        <article class="info-card">
            <h2 class="section-title">Why Choose Us</h2>
            <ul class="list-check mt-3">
                <li>One accountable team for design, build, and implementation.</li>
                <li>Clear project scope, realistic timelines, and transparent updates.</li>
                <li>Experience serving organizations around Kitintale Road and wider Kampala.</li>
                <li>Balanced expertise in both digital systems and physical brand production.</li>
            </ul>
        </article>
    </section>

    <section class="content-section info-card">
        <h2 class="section-title">Frequently Asked Questions</h2>
        <div class="mt-5 space-y-3">
            <details class="rounded-xl border border-[color:rgba(31,181,111,0.22)] p-4">
                <summary class="cursor-pointer font-heading text-lg">Do you work with small businesses and schools?</summary>
                <p class="mt-2 text-sm leading-7 muted-copy">Yes. We support SMEs, schools, ministries, and growing organizations with practical solutions that match budget and operational needs.</p>
            </details>
            <details class="rounded-xl border border-[color:rgba(31,181,111,0.22)] p-4">
                <summary class="cursor-pointer font-heading text-lg">Can you handle both branding and software projects?</summary>
                <p class="mt-2 text-sm leading-7 muted-copy">Yes. Our team provides graphics, printing, website design, web development, and system development under one coordinated process.</p>
            </details>
            <details class="rounded-xl border border-[color:rgba(31,181,111,0.22)] p-4">
                <summary class="cursor-pointer font-heading text-lg">How do we start a project with Arosoft?</summary>
                <p class="mt-2 text-sm leading-7 muted-copy">Send your requirements through our <a href="{{ route('contact') }}" class="text-[var(--accent)] underline decoration-transparent transition hover:decoration-[var(--accent)]">contact page</a> or WhatsApp, and we will guide you through scope, timeline, and pricing.</p>
            </details>
        </div>
    </section>

    <section class="content-section shell-card rounded-3xl p-8 sm:p-10">
        <h2 class="section-title">Ready to discuss your project?</h2>
        <p class="section-copy mt-3 max-w-3xl">
            We are ready to support your organization with reliable technology, printing, and design services.
        </p>
        <div class="mt-5 flex flex-wrap gap-3">
            <a href="{{ route('contact') }}" class="btn-solid">Get in touch</a>
            <a href="{{ route('services') }}" class="btn-outline">Explore services</a>
        </div>
    </section>
@endsection

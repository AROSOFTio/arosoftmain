@extends('layouts.app')

@section('title', 'AROSOFT | Home')
@section('meta_description', 'Arosoft Innovations Ltd provides IT services, printing, graphics design, IT training, and website/system development from Kitintale Road, Kampala.')
@section('canonical', route('home'))

@section('content')
    <section class="hero-surface p-8 sm:p-10 lg:p-12">
        <div class="hero-grid">
            <div>
                <p class="page-kicker">Arosoft Innovations Ltd</p>
                <h1 class="page-title mt-4">Reliable IT and digital support for growing organizations</h1>
                <p class="section-copy mt-5 max-w-3xl">
                    We design, build, and support practical solutions for schools, businesses, and institutions. From printing and branding
                    to website and system development, we focus on work that is clear, effective, and easy to maintain.
                </p>
                <div class="mt-7 flex flex-wrap gap-3">
                    <a href="{{ route('contact') }}" class="btn-solid">Contact our team</a>
                    <a href="{{ route('services') }}" class="btn-outline">Explore services</a>
                </div>
            </div>

            <aside class="grid gap-3">
                <article class="feature-tile">
                    <h3 class="font-heading">Practical service delivery</h3>
                    <p>Clear scoping, realistic timelines, and responsive support from start to finish.</p>
                </article>
                <article class="feature-tile">
                    <h3 class="font-heading">Full-stack capability</h3>
                    <p>IT services, printing, graphics design, and web development in one coordinated team.</p>
                </article>
                <article class="feature-tile">
                    <h3 class="font-heading">Based in Kampala</h3>
                    <p>Serving clients at Kitintale Road and across Uganda with dependable communication.</p>
                </article>
            </aside>
        </div>
    </section>

    <section class="content-section grid gap-5 md:grid-cols-3">
        <article class="info-card">
            <p class="page-kicker">Printing</p>
            <h2 class="font-heading mt-2 text-2xl">Brand materials that look professional</h2>
            <p class="mt-3 text-sm leading-7 muted-copy">Quality prints for business cards, brochures, packaging, and office branding.</p>
        </article>
        <article class="info-card">
            <p class="page-kicker">Web</p>
            <h2 class="font-heading mt-2 text-2xl">Modern websites and systems</h2>
            <p class="mt-3 text-sm leading-7 muted-copy">Clean interfaces and dependable Laravel development for business workflows.</p>
        </article>
        <article class="info-card">
            <p class="page-kicker">Training</p>
            <h2 class="font-heading mt-2 text-2xl">Hands-on technical growth</h2>
            <p class="mt-3 text-sm leading-7 muted-copy">Courses and internship support designed for real job and project readiness.</p>
        </article>
    </section>
@endsection

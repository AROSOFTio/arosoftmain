@extends('layouts.app')

@section('title', 'Shared Hosting | Arosoft Innovations Ltd')
@section('meta_description', 'Reliable shared hosting for business websites and student systems. Fast onboarding, clear pricing, and upgrade-ready VPS capacity.')
@section('canonical', route('hosting.shared'))

@section('content')
    <section class="hero-surface p-8 sm:p-10 lg:p-12">
        <div class="hero-grid">
            <div>
                <p class="page-kicker">Hosting</p>
                <h1 class="page-title mt-4">Shared Hosting</h1>
                <p class="section-copy mt-5 max-w-3xl">
                    Professional shared hosting for websites, school portals, and web applications.
                    Start small and move to higher capacity when your traffic grows.
                </p>
                <div class="mt-7 flex flex-wrap gap-3">
                    <a href="{{ route('contact') }}" class="btn-solid">Request setup</a>
                    <a href="{{ route('final-year-project-hosting') }}" class="btn-outline">Open FYP hosting</a>
                </div>
            </div>

            <aside class="info-card">
                <p class="page-kicker">Current VPS baseline</p>
                <h2 class="font-heading mt-2 text-2xl">Contabo Cloud VPS 10</h2>
                <ul class="list-check mt-4">
                    <li>4 vCPU cores</li>
                    <li>8 GB RAM</li>
                    <li>75 GB NVMe storage</li>
                    <li>1 snapshot</li>
                </ul>
                <p class="mt-4 text-sm leading-7 muted-copy">
                    This capacity runs your shared hosting today and is upgrade-ready when demand grows.
                </p>
            </aside>
        </div>
    </section>
@endsection

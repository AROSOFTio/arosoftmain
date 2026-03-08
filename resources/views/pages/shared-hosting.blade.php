@extends('layouts.app')

@section('title', 'Shared Hosting | AROSOFT Innovations Ltd')
@section('meta_description', 'Reliable shared hosting for business websites and student systems with fast onboarding and clear service delivery.')
@section('canonical', route('hosting.shared'))

@section('content')
    <section class="hero-surface p-8 sm:p-10 lg:p-12">
        <div class="hero-grid">
            <div>
                <p class="page-kicker">Hosting</p>
                <h1 class="page-title mt-4">Shared Hosting</h1>
                <p class="section-copy mt-5 max-w-3xl">
                    Professional shared hosting for websites, school portals, and web applications.
                    Clean setup, secure deployment, and direct technical support for your service.
                </p>
                <div class="mt-7 flex flex-wrap gap-3">
                    <a href="{{ route('contact') }}" class="btn-solid">Request setup</a>
                    <a href="{{ route('final-year-project-hosting') }}" class="btn-outline">Open FYP hosting</a>
                </div>
            </div>

            <aside class="info-card">
                <p class="page-kicker">Service focus</p>
                <h2 class="font-heading mt-2 text-2xl">What you get</h2>
                <ul class="list-check mt-4">
                    <li>Website and application deployment</li>
                    <li>Domain and DNS support</li>
                    <li>SSL and security setup</li>
                    <li>Monitoring and technical support</li>
                </ul>
                <p class="mt-4 text-sm leading-7 muted-copy">
                    We focus on stable service delivery without exposing infrastructure details.
                </p>
            </aside>
        </div>
    </section>
@endsection

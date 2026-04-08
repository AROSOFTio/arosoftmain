@extends('layouts.app')

@section('title', 'Portfolio | Arosoft Innovations Ltd')
@section('meta_description', 'Selected systems, SaaS platforms, dashboards, and delivery work from Arosoft Innovations Ltd.')
@section('canonical', route('portfolio.index'))

@section('content')
    <section class="hero-surface p-8 sm:p-10 lg:p-12">
        <div class="hero-grid">
            <div>
                <p class="page-kicker">Portfolio</p>
                <h1 class="page-title mt-4">Integrated delivery work inside the main AROSOFT website</h1>
                <p class="section-copy mt-5 max-w-3xl">
                    This portfolio now lives inside the parent site and shows how AROSOFT delivers business systems,
                    SaaS products, operational dashboards, education tools, and process-oriented digital workflows.
                </p>
                <div class="mt-7 flex flex-wrap gap-3">
                    <a href="{{ route('contact') }}" class="btn-solid">Start project discussion</a>
                    <a href="{{ route('services') }}" class="btn-outline">Explore service catalog</a>
                </div>
            </div>

            <aside class="grid gap-3">
                <article class="feature-tile">
                    <h3 class="font-heading">Parent-site continuity</h3>
                    <p>Portfolio, services, blog, tutorials, and tools now point to one connected brand experience.</p>
                </article>
                <article class="feature-tile">
                    <h3 class="font-heading">Current build system retained</h3>
                    <p>The main site continues to run on Laravel, Blade, Vite, Tailwind CSS, and Alpine.</p>
                </article>
                <article class="feature-tile">
                    <h3 class="font-heading">Execution proof</h3>
                    <p>Each case study is framed as operational delivery, not a disconnected gallery page.</p>
                </article>
            </aside>
        </div>
    </section>

    <section class="content-section">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="page-kicker">Case studies</p>
                <h2 class="section-title mt-2">Systems and platforms delivered for real operations</h2>
            </div>
            <p class="text-sm muted-copy">{{ count($projects) }} entries</p>
        </div>

        <div class="mt-6 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($projects as $project)
                <x-portfolio.card :project="$project" />
            @endforeach
        </div>
    </section>
@endsection

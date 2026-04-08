@extends('layouts.app')

@section('title', $project['name'] . ' | Portfolio | Arosoft Innovations Ltd')
@section('meta_description', $project['short_description'])
@section('canonical', route('portfolio.show', ['slug' => $project['slug']]))

@section('content')
    <section class="hero-surface p-8 sm:p-10 lg:p-12">
        <div class="hero-grid">
            <div>
                <a href="{{ route('portfolio.index') }}" class="nav-link-sm">Back to portfolio</a>
                <div class="mt-5 flex flex-wrap gap-3">
                    <span class="rounded-full border border-[color:rgba(17,24,39,0.12)] bg-white px-3 py-1 text-[0.62rem] font-semibold uppercase tracking-[0.16em] muted-faint">
                        {{ $project['category'] }}
                    </span>
                    <span class="rounded-full border border-[color:rgba(17,24,39,0.12)] bg-white px-3 py-1 text-[0.62rem] font-semibold uppercase tracking-[0.16em] muted-faint">
                        {{ $project['year'] }}
                    </span>
                    <span class="rounded-full border border-[color:rgba(17,24,39,0.12)] bg-white px-3 py-1 text-[0.62rem] font-semibold uppercase tracking-[0.16em] muted-faint">
                        {{ $project['status'] }}
                    </span>
                </div>
                <h1 class="page-title mt-5">{{ $project['name'] }}</h1>
                <p class="section-copy mt-5 max-w-3xl">{{ $project['full_description'] }}</p>
                <div class="mt-7 flex flex-wrap gap-3">
                    @if (!empty($project['live_url']))
                        <a href="{{ $project['live_url'] }}" target="_blank" rel="noopener noreferrer" class="btn-solid">Open live project</a>
                    @elseif (!empty($project['demo_url']))
                        <a href="{{ $project['demo_url'] }}" target="_blank" rel="noopener noreferrer" class="btn-solid">Open demo preview</a>
                    @endif
                    <a href="{{ route('contact') }}" class="btn-outline">Discuss a similar build</a>
                </div>
            </div>

            <aside class="grid gap-3">
                <article class="feature-tile">
                    <h3 class="font-heading">Client type</h3>
                    <p>{{ $project['client_type'] }}</p>
                </article>
                <article class="feature-tile">
                    <h3 class="font-heading">Deployment</h3>
                    <p>{{ $project['deployment_type'] }}</p>
                </article>
                <article class="feature-tile">
                    <h3 class="font-heading">Role coverage</h3>
                    <p>{{ implode(', ', $project['roles']) }}</p>
                </article>
            </aside>
        </div>
    </section>

    <section class="content-section grid gap-6 lg:grid-cols-5">
        <article class="info-card lg:col-span-3">
            <h2 class="section-title">Key delivery highlights</h2>
            <ul class="list-check mt-5">
                @foreach ($project['features'] as $feature)
                    <li>{{ $feature }}</li>
                @endforeach
            </ul>
        </article>

        <aside class="info-card lg:col-span-2">
            <h2 class="section-title">Technology stack</h2>
            <div class="mt-5 flex flex-wrap gap-2">
                @foreach ($project['technologies'] as $technology)
                    <span class="rounded-full border border-[color:rgba(17,24,39,0.12)] bg-[color:rgba(249,251,250,0.95)] px-3 py-1 text-[0.64rem] font-semibold uppercase tracking-[0.08em] text-[color:rgba(17,24,39,0.66)]">
                        {{ $technology }}
                    </span>
                @endforeach
            </div>

            @if (!empty($project['tags']))
                <h3 class="font-heading mt-6 text-lg">Focus areas</h3>
                <div class="mt-3 flex flex-wrap gap-2">
                    @foreach ($project['tags'] as $tag)
                        <span class="rounded-full border border-[color:rgba(0,157,49,0.2)] bg-[color:rgba(0,157,49,0.08)] px-3 py-1 text-[0.64rem] font-semibold uppercase tracking-[0.08em] text-[color:rgba(0,122,43,0.9)]">
                            {{ $tag }}
                        </span>
                    @endforeach
                </div>
            @endif
        </aside>
    </section>

    @if (!empty($relatedProjects))
        <section class="content-section">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="page-kicker">Related work</p>
                    <h2 class="section-title mt-2">More delivery examples from the same parent site</h2>
                </div>
                <a href="{{ route('portfolio.index') }}" class="btn-outline">View full portfolio</a>
            </div>

            <div class="mt-6 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($relatedProjects as $relatedProject)
                    <x-portfolio.card :project="$relatedProject" compact />
                @endforeach
            </div>
        </section>
    @endif
@endsection

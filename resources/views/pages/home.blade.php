@extends('layouts.app')

@section('title', 'AROSOFT | Home')
@section('meta_description', 'Arosoft Innovations Ltd provides IT services, printing, graphics design, IT training, and website/system development from Kitintale Road, Kampala.')
@section('canonical', route('home'))

@section('content')
    <section class="hero-surface home-command p-8 sm:p-10 lg:p-12">
        <div class="hero-grid gap-8">
            <div class="relative z-[1]">
                <p class="page-kicker">Arosoft Innovations Ltd</p>
                <h1 class="page-title mt-4">Digital execution partner for systems, content, tools, and growth</h1>
                <p class="section-copy mt-5 max-w-3xl">
                    We combine software delivery, technical content, practical tools, and business systems into one coordinated pipeline.
                    The result is cleaner operations, faster implementation, and stronger digital outcomes.
                </p>
                <div class="mt-7 flex flex-wrap gap-3">
                    <a href="{{ route('contact') }}" class="btn-solid">Book strategy call</a>
                    <a href="{{ route('services') }}" class="btn-outline">Explore service catalog</a>
                </div>
            </div>

            <aside class="home-command-rail grid gap-3">
                <article class="home-signal-card">
                    <p class="page-kicker">Delivery Stack</p>
                    <h3 class="font-heading mt-2 text-xl">Engineering + Operations</h3>
                    <p class="mt-2 text-sm leading-7 muted-copy">From planning to deployment and support with one accountable team.</p>
                </article>
                <article class="home-signal-card">
                    <p class="page-kicker">Content Engine</p>
                    <h3 class="font-heading mt-2 text-xl">Blog + Tutorials</h3>
                    <p class="mt-2 text-sm leading-7 muted-copy">Practical guides and implementation videos that accelerate adoption.</p>
                </article>
                <article class="home-signal-card">
                    <p class="page-kicker">Business Systems</p>
                    <h3 class="font-heading mt-2 text-xl">ERP, POS, CRM, Education</h3>
                    <p class="mt-2 text-sm leading-7 muted-copy">Operational systems aligned to real workflows, not generic templates.</p>
                </article>
            </aside>
        </div>
    </section>

    <section class="content-section home-section-shell">
        <div class="home-section-head">
            <div>
                <p class="page-kicker">Latest Articles</p>
                <h2 class="section-title mt-2">Fresh thinking from our blog desk</h2>
            </div>
            <a href="{{ route('blog') }}" class="btn-outline !w-auto !px-4 !py-2 !text-[0.68rem]">View all posts</a>
        </div>

        @if($latestPosts->isNotEmpty())
            @php
                $leadPost = $latestPosts->first();
                $supportingPosts = $latestPosts->slice(1);
            @endphp

            <div class="grid gap-5 xl:grid-cols-[minmax(0,1.28fr)_minmax(0,1fr)]">
                <x-blog.post-card :post="$leadPost" />

                @if($supportingPosts->isNotEmpty())
                    <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-1">
                        @foreach($supportingPosts as $post)
                            <x-blog.post-card :post="$post" :compact="true" :show-excerpt="false" />
                        @endforeach
                    </div>
                @endif
            </div>
        @else
            <article class="info-card">
                <h3 class="font-heading text-xl">No articles published yet</h3>
                <p class="mt-2 text-sm leading-7 muted-copy">
                    We are preparing practical blog content. Check back soon.
                </p>
            </article>
        @endif
    </section>

    <section class="content-section home-section-shell">
        <div class="home-section-head">
            <div>
                <p class="page-kicker">Tutorials</p>
                <h2 class="section-title mt-2">Recent videos and learning tracks</h2>
            </div>
            <a href="{{ route('tutorials') }}" class="btn-outline !w-auto !px-4 !py-2 !text-[0.68rem]">Open tutorials</a>
        </div>

        @if(!empty($tutorialVideos))
            <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
                @foreach($tutorialVideos as $video)
                    <a
                        href="{{ $video['url'] }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="home-media-card"
                    >
                        <div
                            class="home-media-thumb"
                            style="background-image:url('{{ $video['thumb'] }}')"
                        >
                            <span class="home-media-badge">Video</span>
                        </div>
                        <div class="space-y-2 p-4">
                            <p class="text-sm font-semibold leading-6 text-[color:rgba(17,24,39,0.95)]">
                                {{ \Illuminate\Support\Str::limit($video['title'], 72) }}
                            </p>
                            <p class="text-[0.68rem] uppercase tracking-[0.14em] muted-faint">{{ $video['date'] }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <article class="info-card">
                <h3 class="font-heading text-xl">Tutorial feed loading</h3>
                <p class="mt-2 text-sm leading-7 muted-copy">Video content is temporarily unavailable. Open full tutorials page for updates.</p>
            </article>
        @endif

        @if(!empty($tutorialPlaylists))
            <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                @foreach($tutorialPlaylists as $playlist)
                    <a
                        href="{{ $playlist['url'] }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="home-playlist-pill"
                    >
                        <span class="home-playlist-icon">Play</span>
                        <span class="min-w-0">
                            <span class="block truncate text-sm font-semibold text-[color:rgba(17,24,39,0.94)]">{{ $playlist['title'] }}</span>
                            <span class="block text-[0.68rem] uppercase tracking-[0.14em] muted-faint">{{ $playlist['meta'] ?? 'Playlist' }}</span>
                        </span>
                    </a>
                @endforeach
            </div>
        @endif
    </section>

    <section class="content-section home-section-shell home-systems-shell">
        <div class="home-section-head">
            <div>
                <p class="page-kicker">Systems</p>
                <h2 class="section-title mt-2">Business platforms we deploy and customize</h2>
            </div>
            <a href="{{ route('contact') }}" class="btn-outline !w-auto !px-4 !py-2 !text-[0.68rem]">Request a live demo</a>
        </div>

        <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
            @foreach($systems as $system)
                <article class="home-system-card">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-[0.66rem] uppercase tracking-[0.16em] muted-faint">{{ $system['label'] }}</p>
                        <span class="home-system-status">{{ $system['status'] }}</span>
                    </div>
                    <h3 class="mt-2 font-heading text-xl">{{ $system['name'] }}</h3>
                    <p class="mt-2 text-sm leading-7 muted-copy">{{ $system['summary'] }}</p>

                    <ul class="home-system-modules mt-4">
                        @foreach($system['modules'] as $module)
                            <li>{{ $module }}</li>
                        @endforeach
                    </ul>

                    <a
                        href="{{ $system['url'] }}"
                        @if($system['external']) target="_blank" rel="noopener noreferrer" @endif
                        class="btn-outline mt-5 !w-auto !px-4 !py-2 !text-[0.66rem]"
                    >
                        {{ $system['cta'] }}
                    </a>
                </article>
            @endforeach
        </div>
    </section>

    <section class="content-section home-section-shell">
        <div class="home-section-head">
            <div>
                <p class="page-kicker">Tools</p>
                <h2 class="section-title mt-2">Quick utilities for daily operations</h2>
            </div>
            <a href="{{ route('tools') }}" class="btn-outline !w-auto !px-4 !py-2 !text-[0.68rem]">Open tools arena</a>
        </div>

        @if(!empty($toolHighlights))
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach($toolHighlights as $tool)
                    <a href="{{ route('tools.show', ['slug' => $tool['slug']]) }}" class="home-tool-tile">
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-[0.64rem] uppercase tracking-[0.16em] muted-faint">{{ $tool['category'] }}</p>
                            <span class="home-tool-status">{{ $tool['status'] }}</span>
                        </div>
                        <h3 class="mt-2 font-heading text-xl text-[color:rgba(17,24,39,0.95)]">{{ $tool['name'] }}</h3>
                        <p class="mt-2 text-sm leading-7 muted-copy">{{ \Illuminate\Support\Str::limit($tool['tagline'], 92) }}</p>
                        <span class="home-tool-link mt-4 inline-flex">Open tool -></span>
                    </a>
                @endforeach
            </div>
        @endif
    </section>

    <section class="content-section">
        <article class="home-final-cta">
            <p class="page-kicker">Execution Partner</p>
            <h2 class="section-title mt-2">Need one partner for delivery, systems, and digital growth?</h2>
            <p class="mt-4 max-w-3xl text-sm leading-7 muted-copy">
                Tell us your immediate priorities and we will map a practical implementation plan covering systems,
                content, and rollout support.
            </p>
            <div class="mt-6 flex flex-wrap gap-3">
                <a href="{{ route('contact') }}" class="btn-solid">Start project discussion</a>
                <a href="{{ route('services') }}" class="btn-outline">Review service lines</a>
            </div>
        </article>
    </section>
@endsection

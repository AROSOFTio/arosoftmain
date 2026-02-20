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
            <div class="home-card-list home-card-list--321">
                @foreach($latestPosts as $post)
                    <article class="home-compact-card">
                        <a href="{{ route('blog.show', $post->slug) }}" class="home-compact-media">
                            @if($post->featuredImageUrl())
                                <span class="home-compact-thumb" style="background-image:url('{{ $post->featuredImageUrl() }}')">
                                    <span class="home-compact-badge">Article</span>
                                </span>
                            @else
                                <span class="home-compact-placeholder">
                                    <span class="home-compact-placeholder-label">Blog</span>
                                </span>
                            @endif
                        </a>

                        <div class="home-compact-body">
                            <div class="home-compact-meta">
                                @if($post->category)
                                    <span>{{ $post->category->name }}</span>
                                @endif
                                @if($post->published_at)
                                    <span>{{ $post->published_at->format('M d, Y') }}</span>
                                @endif
                                <span>{{ $post->reading_time_minutes ?: 1 }} min read</span>
                            </div>

                            <h3 class="home-compact-title">
                                <a href="{{ route('blog.show', $post->slug) }}">{{ \Illuminate\Support\Str::limit($post->title, 95) }}</a>
                            </h3>

                            <p class="home-compact-excerpt">
                                {{ \Illuminate\Support\Str::words(strip_tags($post->excerpt ?: (string) $post->body), 12) }}
                            </p>

                            <div class="home-compact-footer">
                                <span class="home-compact-author">By {{ $post->author?->name ?? 'Arosoft Team' }}</span>
                                <a href="{{ route('blog.show', $post->slug) }}" class="home-compact-link">Read -></a>
                            </div>
                        </div>
                    </article>
                @endforeach
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
            <div class="home-video-grid">
                @foreach(array_slice($tutorialVideos, 0, 4) as $video)
                    <a
                        href="{{ $video['url'] }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="home-video-card"
                    >
                        <span class="home-video-title">{{ \Illuminate\Support\Str::limit($video['title'], 96) }}</span>
                        <span class="home-video-thumb" style="background-image:url('{{ $video['thumb'] }}')">
                            <span class="home-video-badge">Video</span>
                        </span>
                        <span class="home-video-meta">
                            <span>YouTube Tutorial</span>
                            <span>{{ $video['date'] }}</span>
                            <span class="home-video-link">Watch -></span>
                        </span>
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

        <div class="home-card-list home-card-list--two">
            @foreach($systems as $system)
                <article class="home-compact-card {{ $loop->even ? 'is-reverse' : '' }}">
                    <div class="home-compact-media">
                        <div class="home-compact-placeholder">
                            <span class="home-compact-placeholder-label">{{ $system['label'] }}</span>
                        </div>
                    </div>

                    <div class="home-compact-body">
                        <div class="home-compact-meta">
                            <span>System</span>
                            <span>{{ $system['status'] }}</span>
                        </div>
                        <h3 class="home-compact-title">{{ $system['name'] }}</h3>
                        <p class="home-compact-excerpt">{{ \Illuminate\Support\Str::words($system['summary'], 17) }}</p>

                        <div class="home-chip-row">
                            @foreach($system['modules'] as $module)
                                <span class="home-chip">{{ $module }}</span>
                            @endforeach
                        </div>

                        <div class="home-compact-footer">
                            <span class="home-compact-author">{{ $system['label'] }}</span>
                            <a
                                href="{{ $system['url'] }}"
                                @if($system['external']) target="_blank" rel="noopener noreferrer" @endif
                                class="home-compact-link"
                            >
                                {{ $system['cta'] }} ->
                            </a>
                        </div>
                    </div>
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
            <div class="home-card-list home-card-list--two">
                @foreach($toolHighlights as $tool)
                    <a href="{{ route('tools.show', ['slug' => $tool['slug']]) }}" class="home-compact-card {{ $loop->even ? 'is-reverse' : '' }}">
                        <span class="home-compact-media">
                            <span class="home-compact-placeholder">
                                <span class="home-compact-placeholder-label">{{ $tool['category'] }}</span>
                            </span>
                        </span>

                        <span class="home-compact-body">
                            <span class="home-compact-meta">
                                <span>Tool</span>
                                <span>{{ $tool['status'] }}</span>
                            </span>
                            <span class="home-compact-title">{{ $tool['name'] }}</span>
                            <span class="home-compact-excerpt">{{ \Illuminate\Support\Str::words($tool['tagline'], 16) }}</span>
                            <span class="home-compact-footer">
                                <span class="home-compact-author">{{ $tool['category'] }}</span>
                                <span class="home-compact-link">Open tool -></span>
                            </span>
                        </span>
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

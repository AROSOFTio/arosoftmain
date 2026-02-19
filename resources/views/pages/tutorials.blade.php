@php
    $channelUrl = (string) config('tutorials.youtube_channel_url', 'https://www.youtube.com/@bentech_ds');
    $channelPlaylistsUrl = rtrim($channelUrl, '/').'/playlists';
@endphp

@extends('layouts.app')

@section('title', 'Latest Tutorials | Arosoft')
@section('meta_description', 'Watch the latest tutorials from the Arosoft YouTube channel.')
@section('canonical', route('tutorials'))

@section('content')
    <section class="hero-surface p-8 sm:p-10">
        <p class="page-kicker">Tutorials</p>
        <h1 class="page-title mt-4">Latest videos from our YouTube channel</h1>
        <p class="section-copy mt-4 max-w-3xl">
            Fresh implementation videos pulled directly from our channel feed.
        </p>
        <div class="mt-6">
            <a href="{{ $channelUrl }}" target="_blank" rel="noopener noreferrer" class="btn-solid !w-auto !px-5">
                Open YouTube Channel
            </a>
        </div>
    </section>

    <section class="content-section">
        <x-adsense.unit
            slot="8082861654"
            format="auto"
            :full-width-responsive="true"
            style="display:block"
            wrapperClass="info-card"
        />
    </section>

    @if(!empty($tutorialVideos))
        <section class="content-section grid gap-5 md:grid-cols-2 xl:grid-cols-3">
            @foreach($tutorialVideos as $video)
                <a
                    href="{{ $video['url'] }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="group overflow-hidden rounded-2xl border border-[color:rgba(17,24,39,0.12)] bg-white transition duration-200 hover:border-[color:rgba(0,157,49,0.42)] hover:shadow-[0_12px_30px_rgba(17,24,39,0.1)]"
                >
                    <div class="aspect-video w-full bg-[color:rgba(17,24,39,0.06)] bg-cover bg-center" style="background-image:url('{{ $video['thumb'] }}')"></div>
                    <div class="space-y-2 p-4">
                        <p class="text-sm font-semibold leading-6 text-[color:rgba(17,24,39,0.92)]">
                            {{ $video['title'] }}
                        </p>
                        <p class="text-[0.68rem] uppercase tracking-[0.14em] muted-faint">{{ $video['date'] }}</p>
                    </div>
                </a>
            @endforeach
        </section>
    @else
        <section class="content-section">
            <article class="info-card">
                <h2 class="font-heading text-2xl">No tutorials available right now</h2>
                <p class="mt-2 text-sm leading-7 muted-copy">
                    We could not load recent videos at the moment. Please check again shortly or open the channel directly.
                </p>
                <a href="{{ $channelUrl }}" target="_blank" rel="noopener noreferrer" class="btn-outline mt-5 !w-auto !px-4">
                    Open YouTube Channel
                </a>
            </article>
        </section>
    @endif

    <section class="content-section">
        <x-adsense.unit
            slot="4422776996"
            format="autorelaxed"
            style="display:block"
            wrapperClass="info-card"
        />
    </section>

    @if(!empty($tutorialPlaylists))
        <section class="content-section">
            <div class="mb-4 flex items-center justify-between gap-4">
                <h2 class="font-heading text-2xl text-[color:rgba(17,24,39,0.92)]">Popular playlists</h2>
                <a href="{{ $channelPlaylistsUrl }}" target="_blank" rel="noopener noreferrer" class="btn-outline !w-auto !px-4">
                    View all playlists
                </a>
            </div>
            <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                @foreach($tutorialPlaylists as $playlist)
                    <a
                        href="{{ $playlist['url'] }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="group overflow-hidden rounded-2xl border border-[color:rgba(17,24,39,0.12)] bg-white transition duration-200 hover:border-[color:rgba(0,157,49,0.42)] hover:shadow-[0_12px_30px_rgba(17,24,39,0.1)]"
                    >
                        <div class="aspect-video w-full bg-[color:rgba(17,24,39,0.06)] bg-cover bg-center" style="background-image:url('{{ $playlist['thumb'] ?? '' }}')"></div>
                        <div class="space-y-2 p-4">
                            <p class="text-sm font-semibold leading-6 text-[color:rgba(17,24,39,0.92)]">
                                {{ $playlist['title'] }}
                            </p>
                            <p class="text-[0.68rem] uppercase tracking-[0.14em] muted-faint">{{ $playlist['meta'] ?? 'Playlist' }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif
@endsection

@php
    $canonical = $post->canonical_url ?: route('blog.show', $post->slug);
    $metaTitle = $post->meta_title ?: ($post->title.' | AROSOFT Blog');
    $metaDescription = $post->meta_description ?: ($post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($post->body), 160));
    $metaKeywords = $post->meta_keywords ?: 'Arosoft blog, software engineering, IT insights';
    $robots = $post->robots ?: 'index,follow';
    $ogTitle = $post->og_title ?: $metaTitle;
    $ogDescription = $post->og_description ?: $metaDescription;
    $ogImage = $post->ogImageUrl() ?: url('/og-image.jpg');
    $shareUrl = route('blog.show', $post->slug);
    $shareTitle = $post->title;
    $shareText = $post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($post->body), 140);
    $encodedShareUrl = urlencode($shareUrl);
    $encodedShareTitle = urlencode($shareTitle);
    $encodedShareText = urlencode($shareText);

    $shareLinks = [
        [
            'label' => 'X',
            'url' => 'https://x.com/intent/tweet?text='.$encodedShareTitle.'&url='.$encodedShareUrl,
        ],
        [
            'label' => 'Facebook',
            'url' => 'https://www.facebook.com/sharer/sharer.php?u='.$encodedShareUrl,
        ],
        [
            'label' => 'LinkedIn',
            'url' => 'https://www.linkedin.com/sharing/share-offsite/?url='.$encodedShareUrl,
        ],
        [
            'label' => 'WhatsApp',
            'url' => 'https://wa.me/?text='.$encodedShareTitle.'%20'.$encodedShareUrl,
        ],
        [
            'label' => 'Email',
            'url' => 'mailto:?subject='.$encodedShareTitle.'&body='.$encodedShareText.'%0A%0A'.$encodedShareUrl,
        ],
    ];

    $blogPostingSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'BlogPosting',
        'headline' => $post->title,
        'description' => $metaDescription,
        'image' => [$ogImage],
        'datePublished' => optional($post->published_at)->toIso8601String(),
        'dateModified' => optional($post->updated_at)->toIso8601String(),
        'author' => [
            '@type' => 'Person',
            'name' => $post->author?->name ?? 'Arosoft Team',
        ],
        'publisher' => [
            '@type' => 'Organization',
            'name' => 'Arosoft Innovations Ltd',
            'logo' => [
                '@type' => 'ImageObject',
                'url' => url('/android-chrome-512x512.png'),
            ],
        ],
        'mainEntityOfPage' => [
            '@type' => 'WebPage',
            '@id' => $canonical,
        ],
    ];

    $breadcrumbSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => collect($breadcrumbs)->values()->map(function ($crumb, $index) {
            return [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $crumb['name'],
                'item' => $crumb['url'],
            ];
        })->all(),
    ];
@endphp

@extends('layouts.app')

@section('meta_title', $metaTitle)
@section('meta_description', $metaDescription)
@section('meta_keywords', $metaKeywords)
@section('canonical', $canonical)
@section('meta_robots', $robots)
@section('og_type', 'article')
@section('og_title', $ogTitle)
@section('og_description', $ogDescription)
@section('og_image', $ogImage)
@section('twitter_card', 'summary_large_image')
@section('twitter_title', $ogTitle)
@section('twitter_description', $ogDescription)
@section('twitter_image', $ogImage)

@section('schema')
    <script type="application/ld+json">
        {!! json_encode($blogPostingSchema, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}
    </script>
    <script type="application/ld+json">
        {!! json_encode($breadcrumbSchema, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}
    </script>
@endsection

@section('content')
    <section class="hero-surface p-8 sm:p-10">
        <nav aria-label="Breadcrumb" class="text-sm muted-faint">
            <ol class="flex flex-wrap items-center gap-2">
                @foreach($breadcrumbs as $crumb)
                    <li class="flex items-center gap-2">
                        <a href="{{ $crumb['url'] }}" class="hover:text-[color:var(--accent)]">{{ $crumb['name'] }}</a>
                        @if(!$loop->last)
                            <span>/</span>
                        @endif
                    </li>
                @endforeach
            </ol>
        </nav>

        <h1 class="page-title mt-4">{{ $post->title }}</h1>

        <div class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm muted-faint">
            <span>By {{ $post->author?->name ?? 'Arosoft Team' }}</span>
            <span>{{ optional($post->published_at)->format('M d, Y') }}</span>
            <span>{{ $post->reading_time_minutes ?: 1 }} min read</span>
            <span>{{ number_format((int) $post->view_count) }} views</span>
            @if($post->category)
                <a href="{{ route('blog.category', $post->category->slug) }}" class="nav-link-sm !px-2 !py-1">{{ $post->category->name }}</a>
            @endif
        </div>
    </section>

    <section class="content-section grid gap-8 lg:grid-cols-[minmax(0,1fr)_22rem]">
        <article class="space-y-7">
            @if($post->featuredImageUrl())
                <figure class="overflow-hidden rounded-2xl border border-[color:rgba(17,24,39,0.1)]">
                    <img
                        src="{{ $post->featuredImageUrl() }}"
                        alt="{{ $post->featured_image_alt ?: $post->title }}"
                        loading="lazy"
                        class="h-auto w-full object-cover"
                    >
                </figure>
            @endif

            <div class="shell-card rounded-2xl p-6 sm:p-8">
                <div class="blog-prose">
                    {!! $bodyWithInline !!}
                </div>
            </div>

            <x-adsense.unit
                slot="8137319086"
                format="fluid"
                layout="in-article"
                style="display:block; text-align:center;"
                wrapperClass="info-card"
            />

            @if($post->tags->isNotEmpty())
                <div class="info-card">
                    <h2 class="font-heading text-lg">Tags</h2>
                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach($post->tags as $tag)
                            <a href="{{ route('blog.tag', $tag->slug) }}" class="nav-link-sm !py-1 !px-3">
                                #{{ $tag->name }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="info-card">
                <p class="page-kicker">Share</p>
                <h2 class="mt-2 font-heading text-xl">Share this article</h2>
                <div class="mt-4 flex flex-wrap gap-2">
                    @foreach($shareLinks as $share)
                        <a
                            href="{{ $share['url'] }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="btn-outline !w-auto !px-4 !py-2 !text-[0.66rem]"
                        >
                            {{ $share['label'] }}
                        </a>
                    @endforeach
                    <button
                        type="button"
                        class="btn-outline !w-auto !px-4 !py-2 !text-[0.66rem]"
                        data-copy-share-url="{{ $shareUrl }}"
                    >
                        Copy Link
                    </button>
                </div>
                <p class="mt-2 text-xs muted-faint" data-copy-share-feedback hidden>Link copied.</p>
            </div>

            <div class="info-card">
                <p class="page-kicker">Author</p>
                <h2 class="mt-2 font-heading text-xl">{{ $post->author?->name ?? 'Arosoft Team' }}</h2>
                <p class="mt-2 text-sm leading-7 muted-copy">
                    Arosoft delivery team focused on practical implementation guides, platform updates, and technical operations.
                </p>
            </div>

            @if($relatedPosts->isNotEmpty())
                <div class="space-y-4">
                    <h2 class="section-title text-2xl">Related posts</h2>
                    <div class="grid gap-5 md:grid-cols-2">
                        @foreach($relatedPosts as $relatedPost)
                            <x-blog.post-card :post="$relatedPost" :compact="true" />
                        @endforeach
                    </div>
                </div>
            @endif

            <x-adsense.unit
                slot="8593659207"
                format="fluid"
                layoutKey="-h6-1e-31+19+jo"
                style="display:block"
                wrapperClass="info-card"
            />
        </article>

        <aside class="hidden lg:block">
            <x-blog.sidebar
                :categories="$sidebar['categories']"
                :tags="$sidebar['tags']"
                :popular-posts="$sidebar['popularPosts']"
                :latest-posts="$sidebar['latestPosts']"
                :query-text="''"
            />
        </aside>
    </section>

    <section class="mt-8 block lg:hidden">
        <x-blog.sidebar
            :categories="$sidebar['categories']"
            :tags="$sidebar['tags']"
            :popular-posts="$sidebar['popularPosts']"
            :latest-posts="$sidebar['latestPosts']"
            :query-text="''"
        />
    </section>
@endsection

@push('head')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-copy-share-url]').forEach(function (button) {
                button.addEventListener('click', async function () {
                    const url = button.getAttribute('data-copy-share-url');
                    if (!url || !navigator.clipboard) {
                        return;
                    }

                    try {
                        await navigator.clipboard.writeText(url);

                        const card = button.closest('.info-card');
                        if (!card) {
                            return;
                        }

                        const feedback = card.querySelector('[data-copy-share-feedback]');
                        if (!feedback) {
                            return;
                        }

                        feedback.hidden = false;
                        window.setTimeout(function () {
                            feedback.hidden = true;
                        }, 1600);
                    } catch (error) {
                        // Clipboard write can fail in unsupported contexts; ignore silently.
                    }
                });
            });
        });
    </script>
@endpush

@php
    $canonical = $post->canonical_url ?: route('blog.show', $post->slug);
    $metaTitle = $post->meta_title ?: ($post->title.' | AROSOFT Blog');
    $metaDescription = $post->meta_description ?: ($post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($post->body), 160));
    $metaKeywords = $post->meta_keywords ?: 'Arosoft blog, software engineering, IT insights';
    $robots = $post->robots ?: 'index,follow';
    $ogTitle = $post->og_title ?: $metaTitle;
    $ogDescription = $post->og_description ?: $metaDescription;
    $ogImage = $post->ogImageUrl() ?: url('/og-image.jpg');

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
                'url' => url('/favicon.ico'),
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


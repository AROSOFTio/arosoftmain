@php
    $metaTitle = 'AROSOFT Blog | '.$title;
    $metaDescription = $activeCategory?->description
        ?: ($queryText !== '' ? 'Search results from Arosoft blog for '.$queryText.'.' : 'Read Arosoft blog insights, tutorials, and product updates.');
    $selectedCategorySlug = (string) request()->query('category', $activeCategory?->slug ?? '');
@endphp

@extends('layouts.app')

@section('meta_title', $metaTitle)
@section('meta_description', $metaDescription)
@section('meta_keywords', 'Arosoft blog, IT tutorials, web development, technology insights')
@section('canonical', url()->current().(request()->getQueryString() ? '?'.request()->getQueryString() : ''))
@section('og_type', 'website')
@section('og_title', $metaTitle)
@section('og_description', $metaDescription)

@section('content')
    <section class="hero-surface p-8 sm:p-10">
        <p class="page-kicker">Arosoft Blog</p>
        <h1 class="page-title mt-4">{{ $heading }}</h1>
        <p class="section-copy mt-4 max-w-3xl">
            Practical insights from our delivery team on software, design, operations, and digital growth.
        </p>
        <div class="mt-6 flex flex-wrap items-center gap-3">
            @if($activeCategory)
                <span class="nav-link-sm !px-3 !py-1">Category: {{ $activeCategory->name }}</span>
            @endif
            @if($activeTag)
                <span class="nav-link-sm !px-3 !py-1">Tag: #{{ $activeTag->name }}</span>
            @endif
            @if($queryText !== '')
                <span class="nav-link-sm !px-3 !py-1">Search: {{ $queryText }}</span>
            @endif
            <a href="{{ route('blog') }}" class="btn-outline !w-auto !px-4 !py-2 !text-[0.68rem]">Reset filters</a>
        </div>
    </section>

    <section class="content-section grid gap-8 lg:grid-cols-[minmax(0,1fr)_22rem]">
        <div class="space-y-7">
            <section class="info-card">
                <form action="{{ route('blog') }}" method="get" class="grid gap-3 md:grid-cols-12">
                    <div class="md:col-span-5">
                        <label for="blog-filter-search" class="form-label">Search</label>
                        <input
                            id="blog-filter-search"
                            type="search"
                            name="q"
                            value="{{ $queryText }}"
                            placeholder="Search by title, excerpt, or content"
                            class="form-field"
                        >
                    </div>

                    <div class="md:col-span-3">
                        <label for="blog-filter-category" class="form-label">Category</label>
                        <select id="blog-filter-category" name="category" class="form-field">
                            <option value="">All categories</option>
                            @foreach($sidebar['categories'] as $categoryOption)
                                @php $depth = (int) ($categoryOption->depth ?? 0); @endphp
                                <option
                                    value="{{ $categoryOption->slug }}"
                                    @selected($selectedCategorySlug === (string) $categoryOption->slug)
                                >
                                    {{ str_repeat('-- ', $depth) }}{{ $categoryOption->name }} ({{ $categoryOption->published_posts_count }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label for="blog-filter-sort" class="form-label">Sort</label>
                        <select id="blog-filter-sort" name="sort" class="form-field">
                            <option value="latest" @selected($sort === 'latest')>Latest</option>
                            <option value="popular" @selected($sort === 'popular')>Most viewed</option>
                            <option value="oldest" @selected($sort === 'oldest')>Oldest</option>
                        </select>
                    </div>

                    <div class="md:col-span-2 md:self-end">
                        <div class="flex flex-wrap gap-2 md:justify-end">
                            <button type="submit" class="btn-solid !w-auto !px-4 !py-2 !text-[0.68rem]">Apply</button>
                            <a href="{{ route('blog') }}" class="btn-outline !w-auto !px-4 !py-2 !text-[0.68rem]">Reset</a>
                        </div>
                    </div>
                </form>
            </section>

            @if($featuredPost)
                <div>
                    <p class="page-kicker mb-3">Featured</p>
                    <x-blog.post-card :post="$featuredPost" />
                </div>
            @endif

            @if($posts->count())
                <div class="blog-card-grid grid gap-5 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
                    @foreach($posts as $post)
                        <x-blog.post-card
                            :post="$post"
                            :compact="true"
                            class="blog-card-item"
                            :style="'--blog-card-index: '.($loop->index + 1).';'"
                        />
                    @endforeach
                </div>
            @elseif(!$featuredPost)
                <article class="info-card">
                    <h2 class="font-heading text-2xl">No posts found</h2>
                    <p class="mt-2 text-sm leading-7 muted-copy">Try another keyword, category, or tag.</p>
                </article>
            @endif

            <div class="pt-2">
                {{ $posts->links() }}
            </div>

            <div class="block lg:hidden">
                <x-blog.sidebar
                    :categories="$sidebar['categories']"
                    :tags="$sidebar['tags']"
                    :popular-posts="$sidebar['popularPosts']"
                    :latest-posts="$sidebar['latestPosts']"
                    :query-text="$queryText"
                />
            </div>
        </div>

        <aside class="hidden lg:block">
            <x-blog.sidebar
                :categories="$sidebar['categories']"
                :tags="$sidebar['tags']"
                :popular-posts="$sidebar['popularPosts']"
                :latest-posts="$sidebar['latestPosts']"
                :query-text="$queryText"
            />
        </aside>
    </section>
@endsection

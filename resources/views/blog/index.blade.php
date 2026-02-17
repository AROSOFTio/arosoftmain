@php
    $metaTitle = 'AROSOFT Blog | '.$title;
    $metaDescription = $activeCategory?->description
        ?: ($queryText !== '' ? 'Search results from Arosoft blog for '.$queryText.'.' : 'Read Arosoft blog insights, tutorials, and product updates.');
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
            @if($featuredPost)
                <div>
                    <p class="page-kicker mb-3">Featured</p>
                    <x-blog.post-card :post="$featuredPost" />
                </div>
            @endif

            @if($posts->count())
                <div class="grid gap-5 md:grid-cols-2">
                    @foreach($posts as $post)
                        <x-blog.post-card :post="$post" :compact="true" />
                    @endforeach
                </div>
            @else
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


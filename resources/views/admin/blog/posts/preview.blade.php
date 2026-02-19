@extends('admin.layouts.app')

@section('title', 'Preview')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="page-kicker">Preview</p>
            <h1 class="font-heading text-3xl">{{ $post->title }}</h1>
        </div>
        <a href="{{ route('admin.blog.posts.edit', $post) }}" class="btn-outline !w-auto !px-4 !py-2 !text-[0.68rem]">Back to editor</a>
    </div>

    <article class="space-y-6">
        <div class="admin-card p-5">
            <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-sm muted-faint">
                <span>Status: {{ $post->status === 'draft' ? 'Draft' : 'Published' }}</span>
                <span>{{ optional($post->published_at)->format('M d, Y H:i') ?: 'No publish date' }}</span>
                <span>{{ $post->reading_time_minutes ?: 1 }} min read</span>
                <span>{{ number_format((int) $post->view_count) }} views</span>
            </div>
        </div>

        @if($post->featuredImageUrl())
            <figure class="admin-card overflow-hidden">
                <img src="{{ $post->featuredImageUrl() }}" alt="{{ $post->featured_image_alt ?: $post->title }}" class="h-auto w-full object-cover">
            </figure>
        @endif

        <div class="admin-card p-6 sm:p-8">
            <div class="blog-prose">
                {!! $bodyWithInline !!}
            </div>
        </div>

        @if($bottomRelated->isNotEmpty())
            <section class="space-y-3">
                <h2 class="font-heading text-2xl">Related posts (live site)</h2>
                <div class="grid gap-4 md:grid-cols-2">
                    @foreach($bottomRelated as $relatedPost)
                        <x-blog.post-card :post="$relatedPost" :compact="true" />
                    @endforeach
                </div>
            </section>
        @endif
    </article>
@endsection
